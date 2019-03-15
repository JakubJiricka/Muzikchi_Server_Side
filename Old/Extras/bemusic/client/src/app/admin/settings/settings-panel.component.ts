import {Component, ViewEncapsulation} from "@angular/core";
import {ToastService} from "../../shared/toast/toast.service";
import {ModalService} from "../../shared/modal/modal.service";
import {SettingsState} from "./settings-state.service";
import {Settings} from "../../shared/settings.service";
import {AppHttpClient} from "../../shared/app-http-client.service";
import {Uploads} from "../../shared/uploads.service";
import {ActivatedRoute} from "@angular/router";
import {Pages} from "../pages/pages.service";

@Component({
    selector: 'settings-panel',
    template: '',
    encapsulation: ViewEncapsulation.None,
})
export class SettingsPanelComponent {

    public loading = false;

    constructor(
        public settings: Settings,
        protected toast: ToastService,
        protected http: AppHttpClient,
        protected uploads: Uploads,
        protected modal: ModalService,
        protected route: ActivatedRoute,
        public state: SettingsState,
        protected pages: Pages,
    ) {}

    /**
     * Save current settings to the server.
     */
    public saveSettings(settings?: Object) {
        this.settings.save(settings || this.state.getModified()).subscribe(() => {
            this.toast.show('Saved settings');
        });
    }
}
