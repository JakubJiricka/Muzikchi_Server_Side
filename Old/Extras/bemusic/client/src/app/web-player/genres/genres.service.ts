import {Injectable} from '@angular/core';
import {AppHttpClient} from "../../shared/app-http-client.service";
import {Observable} from "rxjs/Observable";
import {Genre} from "../../shared/types/models/Genre";
import {Artist} from "../../shared/types/models/Artist";
import {PaginationResponse} from "../../shared/types/pagination-response";

@Injectable()
export class Genres {

    /**
     * Genres Service Constructor.
     */
    constructor(private httpClient: AppHttpClient) {}

    /**
     * Get popular genres.
     */
    public getPopular(): Observable<Genre[]> {
        return this.httpClient.get('genres/popular');
    }

    /**
     * Get artists for specified genre.
     */
    public getGenreArtists(name: string, params = {}): Observable<{artistsPagination: PaginationResponse<Artist>, model: Genre}> {
        return this.httpClient.get(`genres/${name}/artists`, params);
    }
}
