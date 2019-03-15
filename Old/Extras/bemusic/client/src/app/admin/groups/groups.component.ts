import {Component, OnDestroy, OnInit, ViewEncapsulation} from "@angular/core";
import {GroupService} from "./group.service";
import {ToastService} from "../../shared/toast/toast.service";
import {ModalService} from "../../shared/modal/modal.service";
import {ConfirmModalComponent} from "../../shared/modal/confirm-modal/confirm-modal.component";
import {CrupdateGroupModalComponent} from "./crupdate-group-modal/crupdate-group-modal.component";
import {AssignUsersToGroupModalComponent} from "./assign-users-to-group-modal/assign-users-to-group-modal.component";
import {CurrentUser} from "../../auth/current-user";
import {UrlAwarePaginator} from "../pagination/url-aware-paginator.service";
import {DataTable} from "../data-table";
import {Group} from "../../shared/types/models/Group";

@Component({
    selector: 'groups',
    templateUrl: './groups.component.html',
    styleUrls: ['./groups.component.scss'],
    providers: [UrlAwarePaginator],
    encapsulation: ViewEncapsulation.None,
})
export class GroupsComponent extends DataTable implements OnInit, OnDestroy {

    /**
     * List of all available groups models.
     */
    public groups: Group[];

    /**
     * Currently selected group.
     */
    public selectedGroup: Group = new Group();

    /**
     * GroupsComponent Constructor.
     */
    constructor(
        private groupService: GroupService,
        private toast: ToastService,
        private modal: ModalService,
        public paginator: UrlAwarePaginator,
        public currentUser: CurrentUser,
    ) {
        super();
    }

    ngOnInit() {
        this.fetchGroups();
    }

    ngOnDestroy() {
        this.paginator.destroy();
    }

    /**
     * Set given group as selected.
     */
    public selectGroup(group: Group) {
        if (this.selectedGroup !== group) {
            this.selectedGroup = group;
            this.paginateGroupUsers(group);
            this.deselectAllItems();
        }
    }

    /**
     * Fetch all existing groups.
     */
    public fetchGroups() {
        this.groupService.getGroups().subscribe(response => {
            this.groups = response.data;

            if (this.groups.length) {

                //if no group is currently selected, select first
                if ( ! this.selectedGroup || ! this.selectedGroup.id) {
                    this.selectGroup(this.groups[0]);

                //if group is selected, try to re-select it with the one returned from server
                } else {
                    for (let i = 0; i < this.groups.length; i++) {
                        if (this.groups[i].id == this.selectedGroup.id) {
                            this.selectedGroup = this.groups[i];
                        }
                    }
                }
            }
        })
    }

    /**
     * Fetch users belonging to give group.
     */
    public paginateGroupUsers(group: Group) {
        this.paginator.paginate('users', {group_id: group.id}).subscribe(response => {
            this.items = response.data;
        });
    }

    /**
     * Delete currently selected group.
     */
    public deleteGroup(group) {
        this.groupService.delete(group.id).subscribe(() => {
            this.selectedGroup = null;
            this.onGroupChange();
        });
    }

    /**
     * Called when group is updated or new one is created.
     */
    public onGroupChange() {
        this.fetchGroups();
    }

    /**
     * Remove users from selected group.
     */
    public removeUsersFromSelectedGroup() {
        this.groupService.removeUsers(this.selectedGroup.id, this.selectedItems.slice()).subscribe(() => {
            this.paginateGroupUsers(this.selectedGroup);
            this.deselectAllItems();
            this.toast.show('Users removed from group.');
        })
    }

    /**
     * Show modal for assigning new users to currently selected group.
     */
    public showAssignUsersModal() {
        this.modal.show(AssignUsersToGroupModalComponent, {group: this.selectedGroup}).onDone.subscribe(() => {
            this.paginateGroupUsers(this.selectedGroup);
        })
    }

    /**
     * Show modal for editing user if user is specified
     * or for creating a new user otherwise.
     */
    public showCrupdateGroupModal(group?: Group) {
        this.modal.show(CrupdateGroupModalComponent, {group}).onDone.subscribe(data => this.onGroupChange());
    }

    /**
     * Ask user to confirm deletion of selected group
     * and delete selected group if user confirms.
     */
    public maybeDeleteGroup(group: Group) {
        this.modal.show(ConfirmModalComponent, {
            title: 'Delete Group',
            body:  'Are you sure you want to delete this group?',
            ok:    'Delete'
        }).onDone.subscribe(() => this.deleteGroup(group));
    }

    /**
     * Ask user to confirm detachment of selected users from
     * currently selected group, and detach them if user confirms.
     */
    public maybeDetachUsers(group: Group) {
        this.modal.show(ConfirmModalComponent, {
            title: 'Remove Users from Group',
            body:  'Are you sure you want to remove selected users from this group?',
            ok:    'Remove'
        }).onDone.subscribe(() => this.removeUsersFromSelectedGroup());
    }
}
