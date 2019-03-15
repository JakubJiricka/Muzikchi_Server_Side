import {Component, Output, EventEmitter, ElementRef, Renderer2, ViewEncapsulation} from '@angular/core';
import {ToastService} from "../../../shared/toast/toast.service";
import {GroupService} from "../group.service";
import {BaseModalClass} from "../../../shared/modal/base-modal";
import {ModalService} from "../../../shared/modal/modal.service";
import {Group} from "../../../shared/types/models/Group";
import {SelectPermissionsModalComponent} from "../../users/select-permissions-modal/select-permissions-modal.component";

@Component({
    selector: 'crupdate-group-modal',
    templateUrl: './crupdate-group-modal.component.html',
    styleUrls: ['./crupdate-group-modal.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class CrupdateGroupModalComponent extends BaseModalClass {
    @Output() public onDone  = new EventEmitter();
    @Output() public onClose = new EventEmitter();

    /**
     * If add new permissions panel is currently active.
     */
    public addNewPermissionsActive = false;

    /**
     * Group model.
     */
    public model: Group;

    /**
     * If we are updating existing group or creating a new one.
     */
    public updating: boolean = false;

    /**
     * CrupdateGroupModalComponent Constructor.
     */
    constructor(
        protected elementRef: ElementRef,
        protected renderer: Renderer2,
        private toast: ToastService,
        private groupService: GroupService,
        private modal: ModalService,
    ) {
        super(elementRef, renderer);
        this.resetState();
    }

    /**
     * Remove given permission from model.
     */
    public removePermission(permission) {
        //need to assign new object instead of editing a reference
        //so angular change detection gets triggered for pipe
        let newPermissions = Object.assign({}, this.model.permissions) as any;
        delete newPermissions[permission];
        this.model.permissions = newPermissions;
    }

    /**
     * Add given permissions to model.
     */
    public addNewPermissions(permissions: string[]) {
        let newPermissions = {};

        permissions.forEach(permission => {
            newPermissions[permission] = 1;
        });

        this.model.permissions = Object.assign({}, this.model.permissions, newPermissions);
    }

    /**
     * Show panel for attaching new permissions to user.
     */
    public showAddPermissionsModal() {
        this.modal.show(SelectPermissionsModalComponent).onDone.subscribe(permissions => {
            this.addNewPermissions(permissions);
        });
    }

    public close() {
        this.resetState();
        super.close();
    }

    public show(params: {group: Group}) {
        this.resetState();

        if (params['group']) {
            this.updating = true;
            this.hydrateModel(params['group']);
        } else {
            this.updating = false;
        }

        super.show(params);
    }

    public confirm() {
        let request;

        if (this.updating) {
            request = this.groupService.update(this.model.id, Object.assign({}, this.model));
        } else {
            request = this.groupService.createNew(Object.assign({}, this.model));
        }

        request.subscribe(response => {
            super.done(response.data);
            this.toast.show('Group '+this.updating ? 'Updated' : 'Created');
            this.close();
        }, this.handleErrors.bind(this));
    }

    /**
     * Reset all modal state to default.
     */
    private resetState() {
        this.model = new Group({'default': 0, permissions: {}});
        this.errors = {};
    }

    /**
     * Populate group model with given data.
     */
    private hydrateModel(group) {
        Object.assign(this.model, group);
    }
}