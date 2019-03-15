import {EventEmitter, Injectable} from '@angular/core';
import {environment} from "../../environments/environment";
import {AppHttpClient} from "./app-http-client.service";

@Injectable()
export class Settings {

    /**
     * HttpClient Service instance.
     */
    private http: AppHttpClient;

    /**
     * Public settings.
     */
    private all = <any>{};

    /**
     * Backend CSRF TOKEN.
     */
    public csrfToken: string;

    /**
     * Fired when any of the settings are changed manually.
     */
    public onChange: EventEmitter<string> = new EventEmitter();

    /**
     * Set multiple settings on settings service.
     */
    public setMultiple(settings: Object) {
        for (let key in settings) {
            let value = settings[key];

            if (value === '0' || value === '1') {
                value = parseInt(value);
            }

            this.set(key, value);
        }
    }

    /**
     * Set single setting.
     */
    public set(name, value, fireEvent = false) {
        this.all[name] = value;
        if (fireEvent) this.onChange.emit(name);
    }

    /**
     * Get a setting by key, optionally providing default value.
     */
    public get(name: string, defaultValue: any = null) {
        let value = this.all[name];

        if (value === undefined) {
            return defaultValue;
        } else {
            return value;
        }
    }

    /**
     * Get all settings.
     */
    public getAll() {
        return this.all;
    }

    /**
     * Check if setting with specified name exists.
     */
    public has(name: string): boolean {
        return this.all[name] != null;
    }

    /**
     * Get a json setting by key and decode it.
     */
    public getJson(name: string, defaultValue: any = null) {
        JSON.parse(this.get(name, defaultValue));
        return JSON.parse(this.get(name, defaultValue));
    }

    /**
     * Get base url for the app.
     */
    public getBaseUrl(forceServerUrl = false): string {
        //sometimes we might need to get base url supplied by backend
        //even in development environment, for example, to prevent
        //uploaded images from having proxy urls like "localhost:4200"
        if (this.has('base_url') && (environment.production || forceServerUrl)) {
            return this.get('base_url') + '/';
        } else if (document.querySelector('base')) {
            return document.querySelector('base')['href'];
        } else {
            return 'http://';
        }
    }

    /**
     * Get default image url for artist or album.
     */
    public getDefaultImage(type: 'artist'|'album'): string {
        if (type === 'artist') {
            return this.getBaseUrl() + 'assets/images/default/artist_small.jpg';
        } else {
            return this.getBaseUrl() + 'assets/images/default/album.png';
        }
    }

    /**
     * Check if any social login is enabled.
     */
    public anySocialLoginEnabled() {
        const names = ['facebook', 'google', 'twitter'];
        return names.filter(name => this.get('social.'+name+'.enable')).length > -1;
    }

    /**
     * Save specified setting on the server.
     */
    public save(params: {client?: Object, server?: Object}) {
        if ( ! params.client && ! params.server) {
            params = {client: params};
        }

        this.setMultiple(params.client);

        const encoded = btoa(encodeURIComponent(JSON.stringify(params)));
        return this.http.post('settings', {settings: encoded})
    }

    /**
     * Set HttpClient instance.
     */
    public setHttpClient(http: AppHttpClient) {
        this.http = http;
    }
}