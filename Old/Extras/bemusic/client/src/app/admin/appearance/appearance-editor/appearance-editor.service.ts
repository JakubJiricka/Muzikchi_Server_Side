import {Injectable} from '@angular/core';
import {AppearancePreview} from "./appearance-preview.service";
import {AppearancePendingChanges} from "./appearance-pending-changes.service";
import * as config from "./config";
import {Settings} from "../../../shared/settings.service";

@Injectable()
export class AppearanceEditor {

    /**
     * Currently active appearance panel.
     */
    public activePanel: string;

    /**
     * All default settings for the application.
     */
    public defaultSettings: {name: string, value: any}[];

    /**
     * Appearance editor configuration.
     */
    public config = [];

    /**
     * AppearanceEditor Constructor.
     */
    constructor(
        public preview: AppearancePreview,
        public changes: AppearancePendingChanges,
        private settings: Settings,
    ) {
        this.config = Object.keys(config).map(function(key) {
            return config[key];
        });
    }

    /**
     * Notify currently active tab to save its changes.
     */
    public saveChanges() {
        this.changes.save();
    }

    /**
     * Close currently active preview panel and navigate to default url.
     */
    public closeActivePanel() {
        this.activePanel = null;
        this.preview.navigateToDefaultRoute();
    }

    /**
     * Get default setting by specified name.
     */
    public getDefaultSetting(name: string) {
        let setting = this.defaultSettings.find(setting => setting.name === name);
        return setting ? setting.value : null;
    }

    /**
     * Initiate appearance editor.
     */
    public init(params) {
        this.preview.init(params);
        this.defaultSettings = params.defaultSettings;
        this.setFieldValues();
    }

    /**
     * Set stored and default values on editable fields.
     */
    private setFieldValues() {
        this.config.forEach(configItem => {
            configItem.fields.forEach(field => {
                field.value = this.getCurrentSetting(field.key);
                field.defaultValue = this.getDefaultSetting(field.key);
            });
        });
    }

    private getCurrentSetting(key: string) {
        if (key.startsWith('env.')) {
            return this.getDefaultSetting('env')[key];
        } else {
            return this.settings.get(key)
        }
    }
}
