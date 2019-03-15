import {Component, ViewEncapsulation} from "@angular/core";
import {SettingsPanelComponent} from "../settings-panel.component";

@Component({
    selector: 'logging-settings',
    templateUrl: './logging-settings.component.html',
    encapsulation: ViewEncapsulation.None,
})
export class LoggingSettingsComponent extends SettingsPanelComponent {}
