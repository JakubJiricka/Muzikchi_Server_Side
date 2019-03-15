import {Component, ViewEncapsulation} from "@angular/core";
import {SettingsPanelComponent} from "../settings-panel.component";

@Component({
    selector: 'permissions-settings',
    templateUrl: './permissions-settings.component.html',
    encapsulation: ViewEncapsulation.None,
})
export class PermissionsSettingsComponent extends SettingsPanelComponent {}
