import {Injectable} from '@angular/core';
import {Observable} from "rxjs/Observable";
import {Localization} from "../types/models/Localization";
import {AppHttpClient} from "../app-http-client.service";
import {Settings} from "../settings.service";

@Injectable()
export class Localizations {

    /**
     * Localizations Constructor.
     */
    constructor(private http: AppHttpClient, private settings: Settings) {}

    /**
     * Get all available  localizations.
     */
    public all(): Observable<Localization[]> {
        return this.http.get('localizations');
    }

    /**
     * Get localization by specified name.
     */
    public get(name: string): Observable<Localization> {
        return this.http.get('localizations/'+name);
    }

    /**
     * Create new localization.
     */
    public create(params: object) {
        return this.http.post('localizations', params);
    }

    /**
     * Update specified localization.
     */
    public update(id: number, params: object) {
        return this.http.put('localizations/'+id, params);
    }

    /**
     * Delete specified localization.
     */
    public delete(id: number) {
        return this.http.delete('localizations/'+id);
    }

    /**
     * Set specified localization as default for new users.
     */
    public setDefault(name: string): Observable<any> {
        let params = {'i18n.default_localization': name};
        return this.settings.save({client: params});
    }
}
