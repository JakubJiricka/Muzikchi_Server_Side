import {Injectable} from '@angular/core';
import {Subscription} from "rxjs/Subscription";
import {Track} from "../shared/types/models/Track";
import {PaginationResponse} from "../shared/types/pagination-response";
import {Album} from "../shared/types/models/Album";
import {Player} from "./player/player.service";
import {Observable} from "rxjs/Observable";

@Injectable()
export abstract class MediaItem<T> {

    /**
     * Whether service is already bootstrapped.
     */
    protected bootstrapped = false;

    /**
     * Media item model.
     */
    protected item: T|any;

    /**
     * Data that is being paginated for media item.
     */
    protected paginatedData: any[] = [];

    /**
     * Last pagination response.
     */
    protected pagination: PaginationResponse<Album|Track>;

    /**
     * Active service subscriptions.
     */
    protected subscriptions: Subscription[] = [];

    /**
     * Whether items are being loaded currently.
     */
    protected loading = false;

    /**
     * MediaItem Service Constructor.
     */
    constructor(protected player: Player) {}

    /**
     * Get media item tracks for player queue.
     */
    public abstract getTracks(paginatedData?: any[]): Track[];

    /**
     * Load media item from backend.
     */
    protected abstract loadItem(id, params: Object): Promise<any>;

    /**
     * Load next page of items that are being paginated.
     */
    protected abstract loadNextPage(id: number, page: number): Observable<PaginationResponse<Album|Track>>;

    /**
     * Play all media item tracks.
     */
    public async play(tracks?: Track[]) {
        if ( ! this.player.queue.itemIsQueued(this.item.id)) {
            this.player.handleContinuousPlayback = false;
            await this.player.overrideQueue({tracks: tracks || this.getTracks(), queuedItemId: this.item.id});
        }

        this.player.play();
    }

    /**
     * Pause the playlist.
     */
    public pause() {
        this.player.pause();
    }

    /**
     * Check if media item is playing currently.
     */
    public playing(item?: T): boolean {
        if ( ! item) item = this.item;
        return this.player.state.playing && this.player.queue.getQueuedItem() === item['id'];
    }

    /**
     * Get the media item.
     */
    public get() {
        return this.item;
    }

    /**
     * Set property on media item object.
     */
    public set(prop: string, value: any) {
        this.item[prop] = value;
    }

    /**
     * Bootstrap the service.
     */
    public init(id: number, params = {}): Promise<any> {
        if (this.bootstrapped && this.item && this.item.id === id) return;
        this.destroy();

        return this.loadItem(id, params).then(response => {
            this.bindToPlaybackEndEvent();
            this.bootstrapped = true;
            return response;
        });
    }

    /**
     * Set item and pagination data on service.
     */
    protected setItem(item: T, pagination: PaginationResponse<Track|Album>) {
        this.item = item;
        this.pagination = pagination;
        this.paginatedData = pagination.data;
    }

    /**
     * Destroy playlist service.
     */
    public destroy() {
        this.unsubscribe();

        this.item = null;
        this.pagination = null;
        this.paginatedData = [];
        this.subscriptions = [];
        this.player.handleContinuousPlayback = true;
    }

    /**
     * Load more tracks for playlist.
     */
    public loadMore(): Promise<any> {
        this.loading = true;
        const page = this.pagination.current_page + 1;

        return this.loadNextPage(this.item.id, page).toPromise().then(response => {
            this.pagination = response;
            this.paginatedData = this.paginatedData.concat(response.data);
            this.player.queue.append(this.getTracks(response.data))
        }).then(() => {
            this.loading = false;
        });
    }

    /**
     * Check if more artists can be loaded for current genre.
     */
    public canLoadMore() {
        return ! this.loading && this.pagination.current_page < this.pagination.last_page;
    }

    /**
     * Lazy load more tracks from user library for
     * continuous playback after current track ends.
     */
    protected bindToPlaybackEndEvent() {
        const sub = this.player.state.onChange$.subscribe(async type => {
            if (type !== 'PLAYBACK_ENDED') return;

            //if player is not playing this artist currently, bail
            if (this.player.queue.getQueuedItem() !== this.item.id) return;

            //load more tracks from user library
            if (this.player.getQueue().isLast() && this.canLoadMore()) {
                await this.loadMore();
            }

            this.player.playNext();
        });

        this.subscriptions.push(sub);
    }

    /**
     * Unsubscribe from all subscriptions this service made.
     */
    protected unsubscribe() {
        this.subscriptions.forEach(subscription => {
            subscription.unsubscribe();
        });
    }
}
