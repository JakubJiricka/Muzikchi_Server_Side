import {Injectable} from '@angular/core';
import {AppHttpClient} from "../../shared/app-http-client.service";
import {Observable} from "rxjs/Observable";
import {Lyric} from "../../shared/types/models/Lyric";

@Injectable()
export class Lyrics {

    /**
     * Lyrics Service Constructor.
     */
    constructor(private http: AppHttpClient) {}

    /**
     * Get lyrics for specified track.
     */
    public get(trackId: number): Observable<Lyric> {
        return this.http.get(`tracks/${trackId}/lyrics`);
    }

    /**
     * Create a new lyric.
     */
    public create(payload: object) {
        return this.http.post('lyrics', payload);
    }

    /**
     * Update specified lyric.
     */
    public update(id: number, payload: object) {
        return this.http.put('lyrics/'+id, payload);
    }

    /**
     * Delete specified lyrics.
     */
    public delete(ids: number[]) {
        return this.http.delete('lyrics', {ids});
    }
}
