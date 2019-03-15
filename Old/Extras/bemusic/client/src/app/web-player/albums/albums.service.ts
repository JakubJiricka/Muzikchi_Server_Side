import {Injectable} from '@angular/core';
import {AppHttpClient} from "../../shared/app-http-client.service";
import {Observable} from "rxjs/Observable";
import {Album} from "../../shared/types/models/Album";

@Injectable()
export class Albums {

    /**
     * Albums Service Constructor.
     */
    constructor(private httpClient: AppHttpClient) {}

    /**
     * Get album matching specified id.
     */
    public get(id: number): Observable<Album> {
        return this.httpClient.get('albums/'+id);
    }

    /**
     * Get popular albums.
     */
    public getPopular(): Observable<Album[]> {
        return this.httpClient.get('albums/popular');
    }

    /**
     * Get new releases.
     */
    public getNewReleases(): Observable<Album[]> {
        return this.httpClient.get('albums/new-releases');
    }

    /**
     * Create a new album.
     */
    public create(payload: object): Observable<Album> {
        return this.httpClient.post('albums', payload);
    }

    /**
     * Update existing album.
     */
    public update(id: number, payload: object): Observable<Album> {
        return this.httpClient.put('albums/'+id, payload);
    }

    /**
     * Delete specified albums.
     */
    public delete(ids: number[]) {
        return this.httpClient.delete('albums', {ids});
    }
}
