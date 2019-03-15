import {Injectable} from '@angular/core';
import {Track} from "../../../shared/types/models/Track";
import {AppHttpClient} from "../../../shared/app-http-client.service";
import {PaginationResponse} from "../../../shared/types/pagination-response";
import {Observable} from "rxjs/Observable";
import {FilterablePage} from "../../filterable-page/filterable-page";
import {Player} from "../../player/player.service";

@Injectable()
export class LibraryTracks extends FilterablePage<Track> {

    /**
     * Ids of all tracks in user library.
     */
    private trackIds: number[] = [];

    /**
     * Current track order.
     */
    public order: string = 'date added';

    /**
     * Current replies infinite load page.
     */
    private currentPage = 1;

    /**
     * Last replies infinite load page.
     */
    private lastPage = 2;

    /**
     * Whether library tracks are being loaded currently.
     */
    private loading = false;

    /**
     * Whether at least one page of library tracks has already been fetched.
     */
    public alreadyFetched = false;

    /**
     * LibraryTracks Service Constructor.
     */
    constructor(private http: AppHttpClient, private player: Player) {
        super();
        this.bindToPlaybackEndEvent();
    }

    /**
     * Get all currently loaded user library tracks.
     */
    public get(): Track[] {
        return this.filteredItems;
    }

    /**
     * Check if user has specified track in library.
     */
    public has(track: Track): boolean {
        return this.trackIds.indexOf(track.id) > -1;
    }

    /**
     * Sort tracks using specified order.
     */
    public sort(order: string) {
        this.order = order;
        this.loadTracks({page: 1}).subscribe(response => {
            this.setPaginationResponse(response);
        });
    }

    /**
     * Add specified tracks to library.
     */
    public add(tracks: Track[]|Track) {
        if ( ! Array.isArray(tracks)) tracks = [tracks];
        const ids = tracks.map(track => track.id);

        this.http.post('user/library/tracks/add', {ids}).subscribe(() => {
            this.prependItems(tracks as Track[]);
            this.trackIds = this.trackIds.concat(ids);
        }, () => {});
    }

    /**
     * Remove specified tracks from library.
     */
    public remove(tracks: Track[]) {
        if ( ! Array.isArray(tracks)) tracks = [tracks];
        const ids = tracks.map(track => track.id);

        this.http.post('user/library/tracks/remove', {ids}).subscribe(() => {
            tracks.forEach(track => {
                this.removeItems(tracks);
                this.trackIds.splice(this.trackIds.findIndex(id => track.id === id), 1);
            });
        }, () => {});
    }

    /**
     * Check if more tracks can be loaded via infinite load.
     */
    public canLoadMoreTracks() {
        return ! this.loading && ! this.filterQuery.value && this.currentPage < this.lastPage;
    }

    /**
     * Load next infinite load page.
     */
    public loadMore(): Observable<PaginationResponse<Track>> {
        this.loading = true;
        const request = this.loadTracks({page: this.currentPage + 1});

        request.subscribe(response => {
            this.appendItems(response.data);
        });

        return request;
    }

    /**
     * Set ids of all tracks in user's library.
     */
    public setTrackIds(ids: number[]) {
        if ( ! ids) ids = [];
        this.trackIds = ids.slice();
    }

    /**
     * Reset user library tracks service to initial state.
     */
    public reset() {
        this.trackIds = [];
        this.order = 'date added';
        this.currentPage = 1;
        this.lastPage = 2;
        this.loading = false;
        this.alreadyFetched = false;
    }

    /**
     * Set specified tracks and sync with player queue.
     */
    protected setItems(tracks: Track[]) {
        super.setItems(tracks);
        this.player.overrideQueue({tracks});
    }

    /**
     * append specified tracks and sync with player queue.
     */
    protected appendItems(tracks: Track[]) {
        super.appendItems(tracks);
        this.player.getQueue().append(tracks);
    }

    /**
     * Set specified filtered tracks (via search) and sync with player queue.
     */
    protected setFilteredItems(tracks: Track[]) {
        super.setFilteredItems(tracks);
        this.player.overrideQueue({tracks});
    }

    /**
     * Filter library tracks by specified query.
     */
    protected filter(query: string) {
        return this.fetch({query}).map(response => response.data);
    }

    /**
     * Load tracks based on specified params.
     */
    private loadTracks(params: { order?: string, page?: number } = {}) {
        this.loading = true;
        params.order = this.order.replace(' ', '_');

        const request = this.fetch(params).share();

        request.subscribe((response: PaginationResponse<Track>) => {
            this.currentPage = response.current_page;
            this.lastPage = response.last_page;
            this.loading = false;
        }, () => {});

        return request;
    }

    /**
     * Get tracks in user's library.
     */
    public fetch(params: { page?: number, query?: string } = {page: 1}): Observable<PaginationResponse<Track>> {
        this.alreadyFetched = true;
        return this.http.get('user/library/tracks', params);
    }

    /**
     * init or reset the service using specified pagination response.
     */
    public setPaginationResponse(response: PaginationResponse<Track>) {
        if ( ! response) return;
        this.currentPage = response.current_page;
        this.lastPage = response.last_page;
        this.setItems(response.data);
    }

    /**
     * Lazy load more tracks from user library for
     * continuous playback after current track ends.
     */
    private bindToPlaybackEndEvent() {
        this.player.state.onChange$.subscribe(async type => {
            if (type !== 'PLAYBACK_ENDED') return;

            //if player is not playing user library currently, bail
            if ( ! this.player.queue.itemIsQueued('library-tracks')) return;

            //load more tracks from user library
            if (this.player.getQueue().isLast() && this.canLoadMoreTracks()) {
                await this.loadMore().toPromise();
            }

            this.player.playNext();
        });
    }
}
