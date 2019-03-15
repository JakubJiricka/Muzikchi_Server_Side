import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {ActivatedRoute} from "@angular/router";
import {Album} from "../../../shared/types/models/Album";
import {WebPlayerUrls} from "../../web-player-urls.service";
import {FormattedDuration} from "../../player/formatted-duration.service";
import {Player} from "../../player/player.service";
import {ContextMenu} from "../../context-menu/context-menu.service";
import {AlbumContextMenuComponent} from "../album-context-menu/album-context-menu.component";
import {WpUtils} from "../../web-player-utils";

@Component({
    selector: 'album',
    templateUrl: './album.component.html',
    styleUrls: ['./album.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class AlbumComponent implements OnInit {

    /**
     * Album to be displayed.
     */
    public album: Album;

    /**
     * Total duration of all album tracks.
     */
    public totalDuration: string;

    /**
     * AlbumComponent Constructor.
     */
    constructor(
        private route: ActivatedRoute,
        public urls: WebPlayerUrls,
        private duration: FormattedDuration,
        private player: Player,
        private contextMenu: ContextMenu
    ) {}

    ngOnInit() {
        this.route.data.subscribe((data: {album: Album}) => {
            this.setAlbum(data.album);
            const total = this.album.tracks.reduce((total, track2) => total + track2.duration, 0);
            this.totalDuration = this.duration.fromMilliseconds(total);
        });
    }

    /**
     * Check if album is playing currently.
     */
    public playing(): boolean {
        return this.player.state.playing && this.player.queue.getQueuedItem() === this.album.id;
    }

    /**
     * Play all album tracks.
     */
    public async play() {
        this.player.stop();
        this.player.state.buffering = true;

        this.player.overrideQueue({
            tracks: this.album.tracks,
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

    /**
     * Open album context menu.
     */
    public openContextMenu(e: MouseEvent) {
        e.stopPropagation();

        this.contextMenu.show(
            AlbumContextMenuComponent,
            {item: this.album, type: 'album'},
            e.target
        );
    }

    /**
     * Set album object on each album track.
     */
    private setAlbum(album: Album) {
        const simplifiedAlbum = Object.assign({}, album, {tracks: []});
        album.tracks = WpUtils.assignAlbumToTracks(album.tracks, simplifiedAlbum);
        this.album = album;
    }
}
