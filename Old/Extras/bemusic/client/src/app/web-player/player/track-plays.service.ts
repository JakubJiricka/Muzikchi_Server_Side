import {Injectable} from '@angular/core';
import {Track} from "../../shared/types/models/Track";
import {Tracks} from "../tracks/tracks.service";

@Injectable()
export class TrackPlays {

    /**
     * Tracks current user has already played.
     */
    private playedTracks: number[] = [];

    /**
     * TrackPlays Constructor.
     */
    constructor(private tracks: Tracks) {}

    /**
     * Increment specified track plays.
     */
    public increment(track: Track) {
        if ( ! track || this.hasBeenPlayed(track)) return;

        this.tracks.incrementPlays(track).subscribe(() => {
            track.plays++;
            this.playedTracks.push(track.id);
        }, () => {});
    }

    /**
     * Clear last track, if it matches specified track.
     * This will allow this track plays to be incremented again.
     */
    public clearPlayedTrack(track: Track) {
        if ( ! track) return;
        this.playedTracks.splice(this.playedTracks.indexOf(track.id), 1);
    }

    /**
     * Check if current user has already incremented plays of specified track.
     */
    private hasBeenPlayed(track: Track): boolean {
        return this.playedTracks.indexOf(track.id) > -1;
    }
}
