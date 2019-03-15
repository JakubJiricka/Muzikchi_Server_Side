import {Component, ViewEncapsulation} from '@angular/core';
import {ModalService} from "../../../shared/modal/modal.service";
import {AppearanceEditor} from "../appearance-editor/appearance-editor.service";
import {ConfirmModalComponent} from "../../../shared/modal/confirm-modal/confirm-modal.component";
import {MenuEditor} from "../menus/menu-editor.service";
import {Menu} from "../menus/menu";
import {AppearancePreview} from "../appearance-editor/appearance-preview.service";

@Component({
    selector: 'menus-appearance-panel',
    templateUrl: './menus-appearance-panel.component.html',
    styleUrls: ['./menus-appearance-panel.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class MenusAppearancePanelComponent {

    /**
     * Whether new menu item panel is currently visible.
     */
    public newMenuItemPanelActive = false;

    /**
     * HelpCenterHomeAppearance Constructor.
     */
    constructor(
        public preview: AppearancePreview,
        public appearance: AppearanceEditor,
        public menus: MenuEditor,
        private modal: ModalService,
    ) {
        this.menus.setFromJson(this.preview.getSetting('menus'));
    }

    /**
     * Toggle new menu item panel visibility.
     */
    public toggleNewMenuItemPanel() {
        this.newMenuItemPanelActive = !this.newMenuItemPanelActive;
    }

    /**
     * Open previous appearance panel.
     */
    public openPreviousPanel() {
        //if menu panel is open, close it
        if (this.menus.activeMenu) {
            this.menus.activeMenu = null;
        }
        //otherwise navigate back to main appearance panel
        else {
            this.appearance.closeActivePanel();
        }
    }

    /**
     * Open specified menu panel.
     */
    public setActiveMenu(menu: Menu) {
        this.menus.activeMenu = menu;
    }

    /**
     * Ask user to confirm menu deletion.
     */
    public confirmMenuDeletion() {
        this.modal.show(ConfirmModalComponent, {
            title: 'Delete Menu',
            body: 'Are you sure you want to delete this menu?',
            ok: 'Delete'
        }).onDone.subscribe(() => this.menus.deleteActive());
    }
}
