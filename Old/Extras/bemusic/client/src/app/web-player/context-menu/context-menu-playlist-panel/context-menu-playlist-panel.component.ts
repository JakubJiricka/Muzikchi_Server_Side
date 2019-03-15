import {Component, EventEmitter, Input, Output, ViewEncapsulation} from '@angular/core';
import {UserPlaylists} from "../../playlists/user-playlists.service";
import {ContextMenu} from "../context-menu.service";
import {ModalService} from "../../../shared/modal/modal.service";
import {Track} from "../../../shared/types/models/Track";
import {Playlists} from "../../playlists/playlists.service";
import {Playlist} from "../../../shared/types/models/Playlist";
import {CurrentUser} from "../../../auth/current-user";
import {Router} from "@angular/router";
import {CrupdatePlaylistModalComponent} from "../../playlists/crupdate-playlist-modal/crupdate-playlist-modal.component";

@Component({
    selector: 'context-menu-playlist-panel',
    templateUrl: './context-menu-playlist-panel.component.html',
    styleUrls: ['./context-menu-playlist-panel.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class ContextMenuPlaylistPanelComponent {

    /**
     * Tracks that should be attached to playlist.
     */
    @Input() tracks: Track[] = [];

    /**
     * Fired when panel should be closed.
     */
    @Output() close$ = new EventEmitter();

    /**
     * ContextMenuPlaylistPanelComponent Constructor.
     */
    constructor(
        public userPlaylists: UserPlaylists,
        private playlists: Playlists,
        public contextMenu: ContextMenu,
        private modal: ModalService,
        private currentUser: CurrentUser,
        private router: Router
    ) {}

    /**
     * Open new playlist modal and attach
     * tracks to newly created playlist.
     */
    public openNewPlaylistModal() {
        this.contextMenu.close();

        if ( ! this.currentUser.isLoggedIn()) {
            return this.router.navigate(['/login']);
        }

        this.modal.show(CrupdatePlaylistModalComponent).onDone.subscribe(playlist => {
            this.userPlaylists.add(playlist);
            this.addTracks(playlist);
        });
    }

    /**
     * Add tracks to specified playlist.
     */
    public addTracks(playlist: Playlist) {
        this.playlists.addTracks(playlist.id, this.tracks).subscribe(() => {
            this.contextMenu.close();
        }, () => {});
    }

    /**
     * Close playlists panel.
     */
    public closePanel() {
        this.close$.emit();
    }
}
