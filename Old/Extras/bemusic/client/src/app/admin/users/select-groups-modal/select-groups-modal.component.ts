import {Component, ElementRef, Renderer2, ViewEncapsulation} from '@angular/core';
import {BaseModalClass} from "../../../shared/modal/base-modal";
import {Group} from "../../../shared/types/models/Group";
import {GroupService} from "../../groups/group.service";

@Component({
    selector: 'select-groups-modal',
    templateUrl: './select-groups-modal.component.html',
    styleUrls: ['./select-groups-modal.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class SelectGroupsModalComponent extends BaseModalClass {

    /**
     * All existing groups.
     */
    public groups: Group[] = [];

    /**
     * Currently selected groups.
     */
    public selectedGroups: number[] = [];

    /**
     * Groups that should not be selectable.
     */
    public disabledGroups: number[] = [];

    /**
     * SelectGroupModalComponent Constructor.
     */
    constructor(
        protected el: ElementRef,
        protected renderer: Renderer2,
        private groupsService: GroupService
    ) {
        super(el, renderer);
    }

    /**
     * Show modal.
     */
    public show(params: {selected?: number[]}) {
        this.fetchAllGroups();
        this.disabledGroups = params.selected;
        super.show(params);
    }

    /**
     * Close modal and return selected groups to caller.
     */
    public confirm() {
        this.done(this.selectedGroups);
    }

    /**
     * Check if given group is currently selected.
     */
    public isGroupSelected(item: number) {
        return this.selectedGroups.indexOf(item) > -1;
    }

    /**
     * Should specified group be disabled (not selectable).
     */
    public isGroupDisabled(id: number) {
        return this.disabledGroups.indexOf(id) > -1;
    }

    /**
     * Selected or deselect specified group.
     */
    public toggleSelectedGroup(item: number) {
        let index = this.selectedGroups.indexOf(item);

        if (index > -1) {
            this.selectedGroups.splice(index, 1);
        } else {
            this.selectedGroups.push(item);
        }
    }

    /**
     * Set all available groups on component,
     * if not provided fetch from the server.
     */
    private fetchAllGroups() {
        this.groupsService.getGroups()
            .subscribe(response => this.groups = response.data);
    }
}
