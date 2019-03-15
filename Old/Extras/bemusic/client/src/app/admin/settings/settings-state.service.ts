import {Injectable} from "@angular/core";

@Injectable()
export class SettingsState {

    /**
     * Initial settings before any changes.
     */
    public initial = {server: {}, client: {}};

    /**
     * Server settings model.
     */
    public server = {};

    /**
     * Client settings model.
     */
    public client = {};

    /**
     * Get settings that have been modified.
     */
    public getModified() {
        return {
            server: this.diffSettingObjects('server'),
            client: this.diffSettingObjects('client'),
        }
    }

    /**
     * Diff specified setting object with initial one.
     */
    private diffSettingObjects(name: string): Object {
        let changed = {};

        for (let key in this[name]) {
            if (this[name][key] !== this.initial[name][key]) {
                changed[key] = this[name][key];
            }
        }

        return changed;
    }

    /**
     * Set all settings on the store.
     */
    public setAll(settings: {server: Object, client: Object}) {
        this.initial = settings;
        this.client = Object.assign({}, settings.client);
        this.server = Object.assign({}, settings.server);
    }
}