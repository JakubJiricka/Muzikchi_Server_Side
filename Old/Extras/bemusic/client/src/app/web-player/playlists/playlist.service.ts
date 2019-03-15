import {Injectable} from '@angular/core';
import {Player} from "../player/player.service";
import {Playlist} from "../../shared/types/models/Playlist";
import {Track} from "../../shared/types/models/Track";
import {Playlists} from "./playlists.service";
import {MediaItem} from "../media-item.service";
import {User} from "../../shared/types/models/User";
import {Settings} from "../../shared/settings.service";

@Injectable()
export class PlaylistService extends MediaItem<Playlist> {

    /**
     * Total duration of playlist in milliseconds.
     */
    public totalDuration: number;

    /**
     * Total number of tracks attached to playlist.
     */
    public tracksCount: number;

    /**
     * PlaylistService Constructor.
     */
    constructor(
        protected playlists: Playlists,
        protected player: Player,
        protected settings: Settings,
    ) {
        super(player);
    }

    public init(id: number, params = {}): Promise<any> {
        this.bindToPlaylistEvents();

        return super.init(id, params).then(data => {
            this.totalDuration = data.totalDuration;
            this.tracksCount = data.playlist.tracks_count;
        });
    }

    /**
     * Get playlist tracks for player queue.
     */
    public getTracks(paginatedData?: Track[]) {
        return paginatedData || this.paginatedData;
    }

    /**
     * Get playlist creator.
     */
    public getCreator(): User {
        return this.item.editors[0];
    }

    /**
     * Get playlist image or first album image.
     */
    public getImage() {
        if (this.item.image) return this.item.image;
        if (this.getTracks().length) return this.getTracks()[0].album.image;
        return this.settings.getBaseUrl(true)+'assets/images/default/artist_small.jpg';
    }

    /**
     * Load playlist from backend.
     */
    protected loadItem(id: number): Promise<any> {
        return this.playlists.get(id).toPromise().then(response => {
            this.setItem(response.playlist, response.tracks);
            return response;
        });
    }

    /**
     * Load next playlist tracks page.
     */
    protected loadNextPage(id: number, page: number) {
        return this.playlists.loadMoreTracks(id, page);
    }

    /**
     * Add or remove tracks from playlist when events are fired.
     */
    public bindToPlaylistEvents() {
        this.unsubscribe();

        //add tracks
        const sub1 = this.playlists.addedTracks$.subscribe(data => {
            if (data.id !== this.item.id) return;
            this.paginatedData = data.tracks.concat(this.paginatedData);
        });

        //remove tracks
        const sub2 = this.playlists.removedTracks$.subscribe(data => {
            if (data.id !== this.item.id) return;

            data.tracks.forEach(track => {
                let i = this.paginatedData.findIndex(curr => curr.id === track.id);
                this.paginatedData.splice(i, 1);
            });
        });

        //update playlist
        const sub3 = this.playlists.updated$.subscribe(playlist => {
            this.item = playlist;
        });

        this.subscriptions = this.subscriptions.concat([sub1, sub2, sub3]);
    }
}
