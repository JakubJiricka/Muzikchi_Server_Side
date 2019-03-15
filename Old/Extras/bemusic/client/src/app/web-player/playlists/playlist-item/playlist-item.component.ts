import {Component, Input, OnDestroy, ViewEncapsulation} from '@angular/core';
import {WebPlayerUrls} from "../../web-player-urls.service";
import {Player} from "../../player/player.service";
import {Playlist} from "../../../shared/types/models/Playlist";
import {PlaylistService} from "../playlist.service";
import {User} from "../../../shared/types/models/User";
import {Settings} from "../../../shared/settings.service";

@Component({
    selector: 'playlist-item',
    templateUrl: './playlist-item.component.html',
    styleUrls: ['./playlist-item.component.scss'],
    encapsulation: ViewEncapsulation.None,
    host: {'class': 'media-grid-item', '[class.active]': 'playing()'},
})
export class PlaylistItemComponent implements OnDestroy {
    @Input() scrollContainer: HTMLElement;

    /**
     * Playlist model.
     */
    @Input() playlist: Playlist;

    /**
     * Creator of playlist.
     */
    @Input() creator: User;

    /**
     * PlaylistItemComponent Constructor
     */
    constructor(
        public urls: WebPlayerUrls,
        private player: Player,
        private playlistService: PlaylistService,
        public settings: Settings
    ) {}

    ngOnDestroy() {
        this.playlistService.destroy();
    }

    /**
     * Check if playlist is currently playing.
     */
    public playing() {
        this.playlistService.playing(this.playlist);
    }

    /**
     * Play all playlist's tracks.
     */
    public async play() {
        this.player.stop();
        this.player.state.buffering = true;
        await this.playlistService.init(this.playlist.id);
        this.playlistService.play();
    }

    /**
     * Pause the playlist.
     */
    public pause() {
        this.player.pause();
    }

    /**
     * Get creator of playlist.
     */
    public getCreator(): User {
        return this.creator || this.playlist.editors[0];
    }
}
