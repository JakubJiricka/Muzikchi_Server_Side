import {Component, ElementRef, Renderer2, ViewEncapsulation} from '@angular/core';
import {BaseModalClass} from "../../../shared/modal/base-modal";
import {ValueLists} from "../../../shared/value-lists.service";

@Component({
    selector: 'select-permissions-modal',
    templateUrl: './select-permissions-modal.component.html',
    styleUrls: ['./select-permissions-modal.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class SelectPermissionsModalComponent extends BaseModalClass {

    /**
     * All available permissions.
     */
    public allPermissions: Object = {};

    /**
     * Permissions that are selected.
     */
    public selectedPermissions: string[] = [];

    /**
     * Permissions that should not be selectable.
     */
    public disabledPermissions: string[] = [];

    /**
     * SelectPermissionsModalComponent Constructor.
     */
    constructor(
        protected el: ElementRef,
        protected renderer: Renderer2,
        private values: ValueLists
    ) {
        super(el, renderer);
    }

    /**
     * Initialize permissions list and open modal.
     */
    public show(params) {
        this.initializePermissions();
        super.show(params);
    }

    /**
     * Close modal and return selected permissions to caller.
     */
    public confirm() {
        this.done(this.selectedPermissions);
    }

    /**
     * Check if given group is currently selected.
     */
    public isPermissionSelected(item: string) {
        return this.selectedPermissions.indexOf(item) > -1;
    }

    /**
     * Should specified group be disabled (not selectable).
     */
    public isPermissionDisabled(id: string) {
        return this.disabledPermissions.indexOf(id) > -1;
    }

    /**
     * Selected or deselect specified group.
     */
    public toggleSelectedPermission(item: string) {
        let index = this.selectedPermissions.indexOf(item);

        if (index > -1) {
            this.selectedPermissions.splice(index, 1);
        } else {
            this.selectedPermissions.push(item);
        }
    }

    /**
     * fetch permissions list from backend.
     */
    private initializePermissions() {
        this.values.getPermissions().subscribe(permissions => {
            this.allPermissions = permissions;
        });
    }
}
