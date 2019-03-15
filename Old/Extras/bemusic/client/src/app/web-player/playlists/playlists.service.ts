import {EventEmitter, Injectable} from '@angular/core';
import {AppHttpClient} from "../../shared/app-http-client.service";
import {Observable} from "rxjs/Observable";
import {Playlist} from "../../shared/types/models/Playlist";
import {Track} from "../../shared/types/models/Track";
import {PaginationResponse} from "../../shared/types/pagination-response";

@Injectable()
export class Playlists {

    /**
     * Fired when tracks are added to a playlist.
     */
    public addedTracks$: EventEmitter<{id: number, tracks: Track[]}> = new EventEmitter();

    /**
     * Fired when tracks are removed from a playlist.
     */
    public removedTracks$: EventEmitter<{id: number, tracks: Track[]}> = new EventEmitter();

    /**
     * Fired when playlist is deleted.
     */
    public deleted$: EventEmitter<number[]> = new EventEmitter();

    /**
     * Fired when existing playlist is updated.
     */
    public updated$: EventEmitter<Playlist> = new EventEmitter();

    /**
     * Playlists Service Constructor.
     */
    constructor(private http: AppHttpClient) {}

    /**
     * Get all current user playlists.
     */
    public allUserPlaylists(id: number): Observable<Playlist[]> {
        return this.http.get(`user/${id}/playlists`);
    }

    /**
     * Paginate all playlists.
     */
    public all(): Observable<PaginationResponse<Playlist>> {
        return this.http.get('playlists');
    }

    /**
     * Get playlist matching specified id.
     */
    public get(id: number): Observable<{playlist: Playlist, tracks: PaginationResponse<Track>}> {
        return this.http.get(`playlists/${id}`);
    }

    /**
     * Create a new playlist.
     */
    public create(params = {}): Observable<Playlist> {
        return this.http.post('playlists', params);
    }

    /**
     * Update specified playlist.
     */
    public update(id: number, params: object = {}): Observable<Playlist> {
        const request = this.http.put(`playlists/${id}`, params).share();
        request.subscribe(playlist => this.updated$.emit(playlist as Playlist), () => {});
        return request as any;
    }

    /**
     * Delete specified playlist.
     */
    public delete(ids: number[]): Observable<any> {
        const request = this.http.delete('playlists', {ids}).share();
        request.subscribe(() =>  this.deleted$.emit(ids), () => {});
        return request;
    }

    /**
     * Follow specified playlist with current user.
     */
    public follow(id: number): Observable<boolean> {
        return this.http.post(`playlists/${id}/follow`);
    }

    /**
     * Unfollow specified playlist with current user.
     */
    public unfollow(id: number): Observable<boolean> {
        return this.http.post(`playlists/${id}/unfollow`);
    }

    /**
     * Attach specified tracks to playlist.
     */
    public addTracks(id: number, tracks: Track[]) {
        let ids = tracks.map(track => track.id);
        this.addedTracks$.emit({id, tracks});
        return this.http.post<Playlist>(`playlists/${id}/tracks/add`, {ids});
    }

    /**
     * Detach specified track from playlist.
     */
    public removeTracks(id: number, tracks: Track[]) {
        let ids = tracks.map(track => track.id);
        this.removedTracks$.emit({id, tracks});
        return this.http.post<Playlist>(`playlists/${id}/tracks/remove`, {ids});
    }

    /**
     * Load more tracks for specified playlist.
     */
    public loadMoreTracks(id: number, page: number = 1): Observable<PaginationResponse<Track>> {
        return this.http.get(`playlists/${id}/tracks`, {page});
    }

    /**
     * Change the order of playlist tracks.
     */
    public changeTrackOrder(id: number, tracks: number[]) {
        return this.http.post(`playlists/${id}/tracks/order`, {ids: tracks});
    }
}
