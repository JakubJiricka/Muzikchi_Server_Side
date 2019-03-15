import {Injectable} from '@angular/core';
import {Playlist} from "../../shared/types/models/Playlist";
import {Playlists} from "./playlists.service";
import {CurrentUser} from "../../auth/current-user";

@Injectable()
export class UserPlaylists {

    /**
     * All user created and followed playlists.
     */
    private playlists: Playlist[] = [];

    /**
     * UserPlaylists Constructor.
     */
    constructor(private playlistsApi: Playlists, private currentUser: CurrentUser) {
        this.bindToPlaylistEvents();
    }

    /**
     * Add specified playlists.
     */
    public add(playlists: Playlist|Playlist[]) {
        if ( ! Array.isArray(playlists)) playlists = [playlists];

        playlists = playlists.filter(playlist => {
            return ! this.playlists.find(curr => curr.id === playlist.id);
        });

        this.playlists = playlists.concat(this.playlists);
    }

    /**
     * Remove specified playlist.
     */
    public remove(playlist: Playlist) {
        let i = this.playlists.findIndex(predicate => predicate.id === playlist.id);
        this.playlists.splice(i, 1);
    }

    /**
     * Get all user playlists.
     */
    public get() {
        return this.playlists;
    }

    /**
     * Set specified playlists on the service.
     */
    public set(playlists: Playlist[]) {
        if ( ! playlists) playlists = [];
        this.playlists = playlists.slice();
    }

    public follow(playlist: Playlist) {
        this.playlistsApi.follow(playlist.id).subscribe(() => {
            this.add(playlist);
        });
    }

    public unfollow(playlist: Playlist) {
        this.playlistsApi.unfollow(playlist.id).subscribe(() => {
            this.remove(playlist);
        });
    }

    /**
     * Check if current user is following specified playlist.
     */
    public following(id: number): boolean {
        const playlist = this.playlists.find(playlist => playlist.id === id);

        //if user is playlist creator, then he is not following it
        return playlist && ! this.isCreator(playlist);
    }

    /**
     * Check if current user is creator of specified playlist.
     */
    public isCreator(playlist: Playlist): boolean {
        if ( ! playlist || ! playlist.editors) return false;
        return playlist.editors.findIndex(user => user.id === this.currentUser.get('id')) > -1;
    }

    /**
     * Reset user playlists service.
     */
    public reset() {
        this.playlists = [];
    }

    private bindToPlaylistEvents() {
        this.playlistsApi.deleted$.subscribe(ids => {
            ids.forEach(id => {
                const i = this.playlists.findIndex(playlist => playlist.id === id);
                this.playlists.splice(i, 1);
            });
        });
    }
}
