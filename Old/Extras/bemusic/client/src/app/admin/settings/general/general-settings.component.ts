import {Component, ViewEncapsulation, OnInit} from "@angular/core";
import {SettingsPanelComponent} from "../settings-panel.component";
import {Page} from "../../../shared/types/models/Page";

@Component({
    selector: 'general-settings',
    templateUrl: './general-settings.component.html',
    encapsulation: ViewEncapsulation.None,
})
export class GeneralSettingsComponent extends SettingsPanelComponent implements OnInit {

    public customPages: Page[] = [];

    ngOnInit() {
        this.pages.getAll().subscribe(response => {
            this.customPages = response.data;
        });
    }
}
