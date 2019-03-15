import {Injectable} from '@angular/core';
import {AppHttpClient} from "../../shared/app-http-client.service";
import {Observable} from "rxjs/Observable";
import {Artist} from "../../shared/types/models/Artist";
import {PaginationResponse} from "../../shared/types/pagination-response";
import {Album} from "../../shared/types/models/Album";
import {Track} from "../../shared/types/models/Track";

@Injectable()
export class Artists {

    /**
     * Artists Service Constructor.
     */
    constructor(private httpClient: AppHttpClient) {}

    /**
     * Get artist matching specified id.
     */
    public get(id: number, params = {}): Observable<{artist: Artist, albums: PaginationResponse<Album>, top_tracks?: Track[]}> {
        return this.httpClient.get('artists/' + id, params);
    }

    /**
     * Create a new artist.
     */
    public create(payload: object): Observable<Artist> {
        return this.httpClient.post('artists', payload);
    }

    /**
     * Update existing artist.
     */
    public update(id: number, payload: object): Observable<Artist> {
        return this.httpClient.put('artists/'+id, payload);
    }

    /**
     * Paginate specified artist albums.
     */
    public paginateArtistAlbums(id: number, page = 1): Observable<PaginationResponse<Album>> {
        return this.httpClient.get('artists/'+id+'/albums', {page});
    }

    /**
     * Get radio recommendations for specified artist.
     */
    public getRadioRecommendations(id: number) {
        return this.httpClient.get(`radio/artist/${id}`);
    }

    /**
     * Delete specified artists.
     */
    public delete(ids: number[]) {
        return this.httpClient.delete('artists', {ids});
    }
}
