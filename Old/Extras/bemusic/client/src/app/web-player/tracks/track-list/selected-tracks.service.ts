import {Injectable} from '@angular/core';
import {Track} from "../../../shared/types/models/Track";

@Injectable()
export class SelectedTracks {

    /**
     * Current selected tracks.
     */
    private tracks: Track[] = [];

    /**
     * Check if specified track is selected.
     */
    public has(track: Track): boolean {
        return this.tracks.findIndex(curr => curr.id === track.id) > -1;
    }

    /**
     * Check if there are no selected tracks currently.
     */
    public empty() {
        return this.tracks.length === 0;
    }

    /**
     * Add track if it is not selected already, otherwise remove it.
     */
    public toggle(track: Track) {
        if (this.has(track)) {
            this.remove(track);
        } else {
            this.add(track);
        }
    }

    /**
     * Select specified track.
     */
    public add(track: Track) {
        if (this.has(track)) return;
        this.tracks.push(track);
    }

    /**
     * Remove selected track.
     */
    public remove(track: Track) {
        const i = this.tracks.findIndex(curr => curr.id === track.id);
        this.tracks.splice(i, 1);
    }

    /**
     * Clear all selected tracks.
     */
    public clear() {
        this.tracks = [];
    }

    /**
     * Return all selected tracks.
     */
    public all() {
        return this.tracks;
    }
}
