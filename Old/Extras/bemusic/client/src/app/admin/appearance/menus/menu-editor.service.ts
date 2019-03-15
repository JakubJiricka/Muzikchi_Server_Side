import {EventEmitter, Injectable} from '@angular/core';
import {Menu} from "./menu";
import {MenuItem} from "./menu-item";
import {AppearancePendingChanges} from "../appearance-editor/appearance-pending-changes.service";
import {AppearancePreview} from "../appearance-editor/appearance-preview.service";
import {utils} from "../../../shared/utils";

@Injectable()
export class MenuEditor {

    private allMenus: Menu[] = [];

    /**
     * Currently selected menu, if any.
     */
    public activeMenu: Menu;

    /**
     * Fired when active menu items change (added or deleted).
     */
    public itemsChange = new EventEmitter();

    /**
     * MenuEditor Constructor.
     */
    constructor(
        private changes: AppearancePendingChanges,
        private preview: AppearancePreview
    ) {}

    /**
     * Get all existing menus.
     */
    public getAll() {
        return this.allMenus;
    }

    /**
     * Create a new menu.
     */
    public create() {
        this.activeMenu = new Menu({name: 'New Menu'});
        this.allMenus.push(this.activeMenu);
        this.commitChanges();
    }

    /**
     * Reorder currently active menu items to specified order.
     */
    public reorderActiveMenuItems(newOrder: number[]) {
        this.activeMenu.items.sort((a, b) => {
            return newOrder.indexOf(a.id) < newOrder.indexOf(b.id) ? -1 : 1;
        });
        this.commitChanges();
    }

    /**
     * Delete currently active menu.
     */
    public deleteActive() {
        let i = this.allMenus.indexOf(this.activeMenu);
        this.allMenus.splice(i, 1);
        this.activeMenu = null;
        this.commitChanges();
    }

    /**
     * Add new menu item to currently active menu.
     */
    public addItem(item: MenuItem) {
        this.activeMenu.items.push(item);
        this.commitChanges();
        this.itemsChange.emit();
    }

    /**
     * Remove specified menu item from currently active menu.
     */
    public deleteMenuItem(item: MenuItem) {
        let i = this.activeMenu.items.indexOf(item);
        this.activeMenu.items.splice(i, 1);
        this.commitChanges();
        this.itemsChange.emit();
    }

    /**
     * Commit current changes to menus.
     */
    public commitChanges() {
        let menus = JSON.stringify(this.allMenus);
        this.changes.add('menus', menus);
        this.preview.setSetting('menus', menus);
    }

    /**
     * Set menus from json string.
     */
    public setFromJson(json: string) {
        if ( ! json) return;
        let menus = JSON.parse(json);

        if ( ! menus) return;

        this.allMenus = menus.map(menuData => {
            let menu = new Menu(menuData);

            menu.items = menu.items.map(item => new MenuItem(item));

            return menu;
        });
    }
}
