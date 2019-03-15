import {Injectable} from '@angular/core';
import {Track} from "../../../shared/types/models/Track";
import {AppHttpClient} from "../../../shared/app-http-client.service";
import {LibraryTracks} from "./library-tracks.service";

@Injectable()
export class UserLibrary {

    /**
     * UserLibrary Service Constructor.
     */
    constructor(private http: AppHttpClient, public tracks: LibraryTracks) {}

    /**
     * Set ids of all tracks in user's library.
     */
    public setTrackIds(ids: number[]) {
        this.tracks.setTrackIds(ids);
    }

    /**
     * Add specified tracks to library.
     */
    public add(tracks: Track|Track[]) {
        this.tracks.add(tracks);
    }

    /**
     * Check if user has specified tracks in library.
     */
    public has(track: Track): boolean {
        return this.tracks.has(track);
    }

    /**
     * Remove specified tracks from user library.
     */
    public remove(tracks: Track[]) {
        this.tracks.remove(tracks);
    }

    /**
     * Fetch all albums in user library.
     */
    public fetchAlbums() {
        return this.http.get('user/library/albums');
    }

    /**
     * Fetch all artists in user library.
     */
    public fetchArtists() {
        return this.http.get('user/library/artists');
    }

    /**
     * Reset user library service to initial state.
     */
    public reset() {
        this.tracks.reset();
    }
}
