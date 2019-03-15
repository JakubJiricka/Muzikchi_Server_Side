import {Component, ViewEncapsulation} from "@angular/core";
import {SettingsPanelComponent} from "../settings-panel.component";

@Component({
    selector: 'envato-settings',
    templateUrl: './envato-settings.component.html',
    encapsulation: ViewEncapsulation.None,
})
export class EnvatoSettingsComponent extends SettingsPanelComponent {

    /**
     * Whether envato items are being imported currently.
     */
    public loading = false;

    /**
     * Import envato items as ticket categories.
     */
    public importEnvatoItems() {
        this.loading = true;

        this.http.post('envato/items/import').subscribe(() => {
            this.toast.show('Imported envato items');
            this.loading = false;
        });
    }

}
