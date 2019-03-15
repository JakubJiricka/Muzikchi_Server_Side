import {Component, OnDestroy, OnInit, ViewEncapsulation} from '@angular/core';
import {DataTable} from "../data-table";
import {UrlAwarePaginator} from "../pagination/url-aware-paginator.service";
import {ModalService} from "../../shared/modal/modal.service";
import {CurrentUser} from "../../auth/current-user";
import {ConfirmModalComponent} from "../../shared/modal/confirm-modal/confirm-modal.component";
import {Playlist} from "../../shared/types/models/Playlist";
import {Settings} from "../../shared/settings.service";
import {Playlists} from "../../web-player/playlists/playlists.service";
import {CrupdatePlaylistModalComponent} from "../../web-player/playlists/crupdate-playlist-modal/crupdate-playlist-modal.component";

@Component({
    selector: 'playlists-page',
    templateUrl: './playlists-page.component.html',
    providers: [UrlAwarePaginator],
    encapsulation: ViewEncapsulation.None
})
export class PlaylistsPageComponent extends DataTable implements OnInit, OnDestroy {

    /**
     * PlaylistsPageComponent Constructor.
     */
    constructor(
        public paginator: UrlAwarePaginator,
        private playlists: Playlists,
        private modal: ModalService,
        public currentUser: CurrentUser,
        private settings: Settings
    ) {
        super();
    }

    /**
     * Fetch initial playlists to display.
     */
    ngOnInit() {
        this.paginator.paginate('playlists').subscribe(response => {
            this.items = response.data;
        });
    }

    ngOnDestroy() {
        this.paginator.destroy();
    }

    /**
     * Show modal for creating a new playlist or updating existing one.
     */
    public showCrupdatePlaylistModal(playlist?: Playlist) {
        this.modal.show(CrupdatePlaylistModalComponent, {playlist}).onDone.subscribe(() => {
            this.deselectAllItems();
            this.paginator.refresh();
        });
    }

    /**
     * Ask user to confirm deletion of selected playlists
     * and delete selected playlists if user confirms.
     */
    public confirmPlaylistsDeletion() {
        this.modal.show(ConfirmModalComponent, {
            title: 'Delete Playlists',
            body: 'Are you sure you want to delete selected playlists?',
            ok: 'Delete'
        }).onDone.subscribe(() => this.deleteSelectedPlaylists());
    }

    /**
     * Delete currently selected pages.
     */
    public deleteSelectedPlaylists() {
        this.playlists.delete(this.selectedItems).subscribe(() => {
            this.paginator.refresh();
            this.deselectAllItems();
        });
    }

    /**
     * Get image for specified playlist.
     */
    public getPlaylistImage(playlist: Playlist): string {
        if (playlist.image) return playlist.image;
        if (playlist.tracks && playlist.tracks.length) return playlist.tracks[0].album.image;
        return this.settings.getDefaultImage('artist');
    }
}
