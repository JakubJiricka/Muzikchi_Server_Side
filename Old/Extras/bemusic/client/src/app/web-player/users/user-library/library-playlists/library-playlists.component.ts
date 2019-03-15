import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {WebPlayerUrls} from "../../../web-player-urls.service";
import {Playlist} from "../../../../shared/types/models/Playlist";
import {Settings} from "../../../../shared/settings.service";
import {ContextMenu} from "../../../context-menu/context-menu.service";
import {PlaylistContextMenuComponent} from "../../../playlists/playlist-context-menu/playlist-context-menu.component";
import {ActivatedRoute} from "@angular/router";

@Component({
    selector: 'library-playlists',
    templateUrl: './library-playlists.component.html',
    styleUrls: ['./library-playlists.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class LibraryPlaylistsComponent implements OnInit {

    /**
     * All current user playlists.
     */
    public playlists: Playlist[] = [];

    /**
     * LibraryPlaylistsComponent Constructor.
     */
    constructor(
        public urls: WebPlayerUrls,
        private settings: Settings,
        private contextMenu: ContextMenu,
        private route: ActivatedRoute
    ) {}

    ngOnInit() {
        this.route.data.subscribe(data => {
            this.playlists = data.playlists;
        });
    }

    /**
     * Get playlist image.
     */
    public getImage(playlist: Playlist) {
        if (playlist.image) return playlist.image;
        if (playlist.tracks && playlist.tracks.length) return playlist.tracks[0].album.image;
        return this.settings.getDefaultImage('artist');
    }

    /**
     * Open playlist context menu.
     */
    public showContextMenu(playlist: Playlist, e: MouseEvent) {
        e.stopPropagation();

        this.contextMenu.show(
            PlaylistContextMenuComponent,
            {item: playlist, extra: {image: this.getImage(playlist)}, type: 'playlist'},
            e.target
        );
    }
}
