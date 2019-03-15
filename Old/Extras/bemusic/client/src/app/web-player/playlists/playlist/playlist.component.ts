import {ChangeDetectorRef, Component, OnDestroy, OnInit, ViewEncapsulation} from '@angular/core';
import {ActivatedRoute} from "@angular/router";
import {WebPlayerUrls} from "../../web-player-urls.service";
import {FormattedDuration} from "../../player/formatted-duration.service";
import {PlaylistService} from "../playlist.service";
import {ContextMenu} from "../../context-menu/context-menu.service";
import {PlaylistContextMenuComponent} from "../playlist-context-menu/playlist-context-menu.component";
import {Playlists} from "../playlists.service";
import {Track} from "../../../shared/types/models/Track";

@Component({
    selector: 'playlist',
    templateUrl: './playlist.component.html',
    styleUrls: ['./playlist.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class PlaylistComponent implements OnInit, OnDestroy {

    /*
     * Playlist model.
     */
    public playlist: PlaylistService;

    /**
     * Formatted duration of playlist.
     */
    public totalDuration: string;

    /**
     * PlaylistComponent Constructor.
     */
    constructor(
        private route: ActivatedRoute,
        public urls: WebPlayerUrls,
        private duration: FormattedDuration,
        private contextMenu: ContextMenu,
        private playlists: Playlists,
        private changeDetector: ChangeDetectorRef
    ) {}

    ngOnInit() {
        this.bindToRouterData();
    }

    ngOnDestroy() {
        this.playlist.destroy();
    }

    /**
     * Reorder playlist tracks using specified order.
     */
    public reorderTracks(tracks: number[]) {
        this.playlists.changeTrackOrder(this.playlist.get().id, tracks).subscribe(() => {
            this.playlist.getTracks().sort(function(a, b) {
                return tracks.indexOf(a.id) - tracks.indexOf(b.id)
            });

            this.changeDetector.detectChanges();
        });
    }

    /**
     * Remove track from currently active playlist.
     */
    public removeTracksFromPlaylist(tracks: Track[]) {
        this.playlists.removeTracks(this.playlist.get().id, tracks).subscribe();
    }

    /**
     * Open playlist context menu.
     */
    public openContextMenu(e: MouseEvent) {
        e.stopPropagation();

        this.contextMenu.show(
            PlaylistContextMenuComponent,
            {item: this.playlist.get(), extra: {image: this.playlist.getImage()}, type: 'playlist'},
            e.target
        );
    }

    /**
     * Set component playlist from resolver.
     */
    private bindToRouterData() {
        this.route.data.subscribe(data => {
            this.playlist = data.playlist;
            this.playlist.bindToPlaylistEvents();
            this.totalDuration = this.duration.toVerboseString(this.playlist.totalDuration);
        });
    }
}
