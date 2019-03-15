import {Component, ViewEncapsulation} from "@angular/core";
import {SettingsPanelComponent} from "../settings-panel.component";

@Component({
    selector: 'authentication-settings',
    templateUrl: './authentication-settings.component.html',
    styleUrls: ['./authentication-settings.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class AuthenticationSettingsComponent extends SettingsPanelComponent {}
