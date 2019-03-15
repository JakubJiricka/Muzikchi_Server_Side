import { Injectable, Injector } from '@angular/core';
import {Settings} from "./shared/settings.service";
import {CurrentUser} from "./auth/current-user";
import {Translations} from "./shared/translations/translations.service";
import {HttpClient} from "@angular/common/http";
import {UserLibrary} from "./web-player/users/user-library/user-library.service";
import {UserPlaylists} from "./web-player/playlists/user-playlists.service";

@Injectable()
export class Bootstrapper {

    /**
     * HttpClient service instance.
     */
    private http: HttpClient;

    /**
     * Settings service instance.
     */
    private settings: Settings;

    /**
     * CurrentUser service instance.
     */
    private currentUser: CurrentUser;

    /**
     * Translations service instance.
     */
    private i18n: Translations;

    /**
     * Boostrapper constructor.
     */
    constructor(private injector: Injector) {
        this.http = this.injector.get(HttpClient);
        this.settings = this.injector.get(Settings);
        this.currentUser = this.injector.get(CurrentUser);
        this.i18n = this.injector.get(Translations);
    }

    /**
     * Bootstrap application with data returned from server.
     */
    public bootstrap(data?: string): Promise<any> {
        if ( ! data) data = window['bootstrapData'];

        //if we have bootstrap data in global scope, pass
        //it to the app and return self resolving promise
        if (data) {
            this.handleData(data);
            return new Promise(resolve => resolve());
        }

        //fetch bootstrap data from backend and return promise that
        //resolves once request is complete and data is passed to the app
        return new Promise((resolve, reject) => {
            let url = this.settings.getBaseUrl() + 'secure/bootstrap-data';
            this.http.get(url).subscribe(response => {
                this.handleData(response['data']);
                resolve();
            }, error => {
                console.log('bootstrap error', error);
                reject();
            });
        });
    }

    /**
     * Handle specified bootstrap data.
     */
    private handleData(encodedData: string) {
        //decode bootstrap data from server
        let data = JSON.parse(atob(encodedData));

        //set csrf token
        this.settings.csrfToken = data['csrf_token'];

        //set all settings returned from server
        this.settings.setMultiple(data['settings']);

        //set translations
        if (data['i18n']) {
            this.i18n.setLocalization(data['i18n']);
        }

        //set current user and default group for guests
        this.currentUser.init({
            guestsGroup: data['guests_group'],
            user: data['user']
        });

        //set user library
        this.injector.get(UserLibrary).setTrackIds(data.tracks);

        //set user playlists
        this.injector.get(UserPlaylists).set(data.playlists);
    }
}