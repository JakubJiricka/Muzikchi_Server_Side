import {Component, ViewEncapsulation} from "@angular/core";
import {SettingsPanelComponent} from "../settings-panel.component";

@Component({
    selector: 'mail-settings',
    templateUrl: './mail-settings.component.html',
    encapsulation: ViewEncapsulation.None,
})
export class MailSettingsComponent extends SettingsPanelComponent {}
