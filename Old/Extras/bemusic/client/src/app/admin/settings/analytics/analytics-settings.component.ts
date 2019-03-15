import {Component, ViewEncapsulation} from "@angular/core";
import {SettingsPanelComponent} from "../settings-panel.component";

@Component({
    selector: 'analytics-settings',
    templateUrl: './analytics-settings.component.html',
    encapsulation: ViewEncapsulation.None,
})
export class AnalyticsSettingsComponent extends SettingsPanelComponent {
}
