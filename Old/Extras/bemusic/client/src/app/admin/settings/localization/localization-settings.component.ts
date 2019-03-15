import {Component, ViewEncapsulation} from "@angular/core";
import {SettingsPanelComponent} from "../settings-panel.component";
import {Localization} from "../../../shared/types/models/Localization";

@Component({
    selector: 'localization-settings',
    templateUrl: './localization-settings.component.html',
    encapsulation: ViewEncapsulation.None,
})
export class LocalizationSettingsComponent extends SettingsPanelComponent {

    /**
     * All available localizations.
     */
    public localizations: Localization[] = [];

    ngOnInit() {
        this.route.data.subscribe(data => {
            this.localizations = data.localizations;
        });
    }

}
