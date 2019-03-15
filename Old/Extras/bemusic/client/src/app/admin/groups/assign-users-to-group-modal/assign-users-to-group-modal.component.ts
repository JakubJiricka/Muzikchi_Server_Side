import {Component, Output, EventEmitter, ElementRef, Renderer2, ViewEncapsulation} from '@angular/core';
import {ToastService} from "../../../shared/toast/toast.service";
import {GroupService} from "../group.service";
import {BaseModalClass} from "../../../shared/modal/base-modal";
import {Group} from "../../../shared/types/models/Group";

@Component({
    selector: 'assign-users-to-group-modal',
    templateUrl: './assign-users-to-group-modal.component.html',
    styleUrls: ['./assign-users-to-group-modal.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class AssignUsersToGroupModalComponent extends BaseModalClass {
    @Output() public onDone  = new EventEmitter();
    @Output() public onClose = new EventEmitter();

    /**
     * Group users should be assigned to.
     */
    public group: Group;

    /*
     * Emails group should be assigned to.
     */
    public emails: any;

    /**
     * AssignUsersToGroupModal Constructor.
     */
    constructor(
        protected elementRef: ElementRef,
        protected renderer: Renderer2,
        private toast: ToastService,
        private groupService: GroupService
    ) {
        super(elementRef, renderer);
        this.resetState();
    }

    public close() {
        this.resetState();
        super.close();
    }

    public show(params) {
        this.resetState();
        this.group = params.group;
        super.show(params);
    }

    public confirm() {
        let emails = this.emails.map(function(obj) {
           return obj.email;
        });

        this.groupService.addUsers(this.group.id, emails).subscribe(() => {
            super.done();
            this.toast.show('Users assigned to group.');
        }, () => this.errors = {emails: true});
    }

    /**
     * Reset all modal state to default.
     */
    private resetState() {
        //empty string is needed for initial input, because we're going
        //to loop through this array and show input for every value.
        this.emails = [{email: ''}];
        this.errors = {};
    }

    /**
     * Add input field to assign one more user to group
     */
    public assignMoreUsers() {
        this.emails.push({email: ''});
    }

    /**
     * Remove assignee at given index.
     */
    public removeUser(index: number) {

        //if there's only one email object, empty it
        if (this.emails.length === 1) {
            this.emails[index].email = '';
        }

        //otherwise remove the whole object (and input)
        else {
            this.emails.splice(index, 1);
        }
    }
}