import {Component, EventEmitter, OnInit, Output, ViewEncapsulation} from '@angular/core';
import {MenuItem} from "../menu-item";
import {MenuEditor} from "../menu-editor.service";
import {AppearanceEditor} from "../../appearance-editor/appearance-editor.service";
import {Pages} from "../../../pages/pages.service";
import {Page} from "../../../../shared/types/models/Page";

@Component({
    selector: 'add-menu-item-panel',
    templateUrl: './add-menu-item-panel.component.html',
    styleUrls: ['./add-menu-item-panel.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class AddMenuItemPanelComponent implements OnInit {

    /**
     * Fired on close panel button click.
     */
    @Output() closeClick = new EventEmitter();

    /**
     * Model for new link item input fields.
     */
    public linkModel: {url?: string, linkText?: string} = {};

    /**
     * Currently active menu items panel.
     */
    public activePanel: string;

    /**
     * All existing, user created, pages.
     */
    public allPages: Page[];

    /**
     * AddMenuItemPanelComponent Constructor.
     */
    constructor(public editor: MenuEditor, public appearance: AppearanceEditor, private pages: Pages) {}

    ngOnInit() {
        this.pages.getAll().subscribe(response => this.allPages = response.data);
    }

    /**
     * Toggle specified menu items panel.
     */
    public togglePanel(name: string) {
        this.activePanel = this.activePanel === name ? null : name;
    }

    /**
     * Add a new link item to currently active menu.
     */
    public addLinkMenuItem() {
        this.editor.addItem(new MenuItem({
            type: 'link',
            label: this.linkModel.linkText,
            action: this.linkModel.url,
        }));

        this.linkModel = {};
    }

    /**
     * Add a new route item to currently active menu.
     */
    public addRouteMenuItem(route: string) {
        this.editor.addItem(new MenuItem({
            type: 'route',
            label: route,
            action: route,
        }));
    }

    /**
     * Add a new page item to currently active menu.
     */
    public addPageMenuItem(page: Page) {
        this.editor.addItem(new MenuItem({
            type: 'page',
            label: page.slug,
            action: page.id + '/' + page.slug,
        }));
    }
}
