import {Component, Injector, ViewEncapsulation} from '@angular/core';
import {ContextMenuComponent} from "../../context-menu/context-menu.component";
import {Playlists} from "../playlists.service";
import {ConfirmModalComponent} from "../../../shared/modal/confirm-modal/confirm-modal.component";
import {UserPlaylists} from "../user-playlists.service";
import {Playlist} from "../../../shared/types/models/Playlist";
import {CrupdatePlaylistModalComponent} from "../crupdate-playlist-modal/crupdate-playlist-modal.component";

@Component({
    selector: 'playlist-context-menu',
    templateUrl: './playlist-context-menu.component.html',
    encapsulation: ViewEncapsulation.None,
    host: {'class': 'context-menu'},
})
export class PlaylistContextMenuComponent extends ContextMenuComponent<Playlist> {

    /**
     * PlaylistContextMenuComponent Constructor.
     */
    constructor(
        protected injector: Injector,
        protected playlists: Playlists,
        protected userPlaylists: UserPlaylists
    ) {
        super(injector);
    }

    public getImage() {
        return this.item.image || (this.params['extra'] && this.params['extra'].image);
    }

    /**
     * Copy fully qualified playlist url to clipboard.
     */
    public copyLinkToClipboard() {
        super.copyLinkToClipboard('playlist');
    }

    /**
     * Get tracks that should be used by context menu.
     */
    public getTracks() {
        return [];
    }

    /**
     * Delete the playlist after user confirms.
     */
    public maybeDeletePlaylist() {
        this.contextMenu.close();

        this.modal.show(ConfirmModalComponent, {
            title: 'Delete Playlist',
            body: 'Are you sure you want to delete this playlist?',
            ok: 'Delete'
        }).onDone.subscribe(() => {
            this.contextMenu.close();
            this.playlists.delete([this.item.id]).subscribe();
            this.maybeNavigateFromPlaylistRoute();
        });
    }

    /**
     * Open playlist edit modal.
     */
    public openEditModal() {
        this.contextMenu.close();
        this.modal.show(CrupdatePlaylistModalComponent, {playlist: this.item});
    }

    /**
     * Check if current user is creator of playlist.
     */
    public userIsCreator() {
        return this.userPlaylists.isCreator(this.item);
    }

    /**
     * Check if current user is following this playlist.
     */
    public userIsFollowing() {
        return this.userPlaylists.following(this.item.id);
    }

    /**
     * Follow this playlist with current user.
     */
    public follow() {
        this.userPlaylists.follow(this.item);
        this.contextMenu.close();
    }

    /**
     * Unfollow this playlist with current user.
     */
    public unfollow() {
        this.userPlaylists.unfollow(this.item);
        this.contextMenu.close();
    }

    /**
     * Check if playlist is private.
     */
    public isPublic() {
        return this.item.public;
    }

    /**
     * Make playlist public.
     */
    public makePublic() {
        this.contextMenu.close();

        return this.playlists.update(this.item.id, {'public': 1}).subscribe(() => {
            this.item.public = 1;
        });
    }

    /**
     * Make playlist private.
     */
    public makePrivate() {
        this.contextMenu.close();

        return this.playlists.update(this.item.id, {'public': 0}).subscribe(() => {
            this.item.public = 0;
        });
    }

    /**
     * Navigate from playlist route if current playlist's route is open.
     */
    private maybeNavigateFromPlaylistRoute() {
        if (this.router.url.indexOf('playlists/'+this.item.id) > -1) {
            this.router.navigate(['/']);
        }
    }
}
