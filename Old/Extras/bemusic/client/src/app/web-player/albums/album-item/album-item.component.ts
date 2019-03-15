import {Component,  Input, ViewEncapsulation} from '@angular/core';
import {WebPlayerUrls} from "../../web-player-urls.service";
import {Album} from "../../../shared/types/models/Album";
import {Albums} from "../albums.service";
import {Player} from "../../player/player.service";
import {WpUtils} from "../../web-player-utils";
import {Settings} from "../../../shared/settings.service";
import {ToastService} from "../../../shared/toast/toast.service";

@Component({
    selector: 'album-item',
    templateUrl: './album-item.component.html',
    styleUrls: ['./album-item.component.scss'],
    encapsulation: ViewEncapsulation.None,
    host: {'class': 'media-grid-item', '[class.active]': 'playing()'},
})
export class AlbumItemComponent {
    @Input() album: Album;
    @Input() scrollContainer: HTMLElement;

    /**
     * AlbumItemComponent Constructor
     */
    constructor(
        public urls: WebPlayerUrls,
        private albums: Albums,
        private player: Player,
        public settings: Settings,
        private toast: ToastService,
    ) {}

    /**
     * Check if album is playing currently.
     */
    public playing() {
        return this.player.state.playing && this.player.queue.getQueuedItem() === this.album.id;
    }

    /**
     * Play all album tracks.
     */
    public async play() {
        this.player.stop();
        this.player.state.buffering = true;

        if ( ! this.album.tracks || ! this.album.tracks.length) {
            this.album = await this.albums.get(this.album.id).toPromise();
        }

        const tracks = WpUtils.assignAlbumToTracks(this.album.tracks, this.album);

        //if could not fetch any tracks for album, bail
        if ( ! tracks.length) {
            this.toast.show('This album has no songs yet.');
            return this.player.state.buffering = false;
        }

        this.player.overrideQueue({
            tracks: tracks,
            queuedItemId: this.album.id
        }).then(() => {
            this.player.play();
        });
    }

    /**
     * Pause the album.
     */
    public pause() {
        this.player.pause();
    }
}
