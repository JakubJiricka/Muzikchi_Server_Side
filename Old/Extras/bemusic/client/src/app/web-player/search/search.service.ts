import {Injectable} from '@angular/core';
import {AppHttpClient} from "../../shared/app-http-client.service";
import {Observable} from "rxjs/Observable";
import {SearchResponse} from "../../shared/types/search-response";

@Injectable()
export class Search {

    /**
     * Search Service Constructor.
     */
    constructor(private http: AppHttpClient) {}

    /**
     * Search for artists, albums, tracks, playlists and users.
     */
    public everything(query: string, params?: {limit: number}): Observable<SearchResponse> {
        return this.http.get('search/'+encodeURIComponent(query), params);
    }

    /**
     * Search for video ID for specified artist and track.
     */
    public videoId(artists: string, track: string): Observable<{title: string, id: string}[]> {
        const artistName = this.encode(artists),
              trackName  = this.encode(track);

        return this.http.get('search/audio/'+artistName+'/'+trackName);
    }

    private encode(string: string): string {
        return encodeURIComponent(string.replace('/', '+'));
    }
}
