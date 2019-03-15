import {Component, ViewEncapsulation} from "@angular/core";
import {SettingsPanelComponent} from "../settings-panel.component";

@Component({
    selector: 'player-settings',
    templateUrl: './player-settings.component.html',
    encapsulation: ViewEncapsulation.None,
})
export class PlayerSettingsComponent extends SettingsPanelComponent {


}
