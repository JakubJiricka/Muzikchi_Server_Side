import {Component, Input, OnInit, ViewEncapsulation} from '@angular/core';
import {AppearanceEditor} from "../appearance-editor/appearance-editor.service";
import {AppearancePreview} from "../appearance-editor/appearance-preview.service";

@Component({
    selector: 'appearance-panel',
    templateUrl: './appearance-panel.component.html',
    styleUrls: ['./appearance-panel.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class AppearancePanelComponent implements OnInit {

    /**
     * config for appearance panel.
     */
    @Input() public config;

    /**
     * AppearancePanelComponent Constructor.
     */
    constructor(public appearance: AppearanceEditor, private preview: AppearancePreview) {}

    ngOnInit() {
        this.preview.navigate(this.config.route);
    }
}
