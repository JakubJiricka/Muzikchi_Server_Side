import {Component, EventEmitter, Input, Output, ViewEncapsulation} from '@angular/core';
import {Player} from "../player.service";
import {Track} from "../../../shared/types/models/Track";

@Component({
    selector: 'playback-control-button',
    templateUrl: './playback-control-button.component.html',
    styleUrls: ['./playback-control-button.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class PlaybackControlButtonComponent {

    /**
     * Fired on play button click.
     */
    @Output() play = new EventEmitter();

    /**
     * Fired on pause button click.
     */
    @Output() pause = new EventEmitter();

    /**
     * Track that this buttons controls.
     */
    @Input() track: Track;

    /**
     * Whether track is currently playing.
     * Parent component can control playback state using this input.
     */
    @Input() playing = null;

    /**
     * PlaybackControlButtonComponent Constructor.
     */
    constructor(private player: Player) {}

    /**
     * Check if specified track is cued and playing.
     */
    public trackIsPlaying() {
        //parent component is controlling playback state.
        if (this.playing !== null) return this.playing;

        //playback state is based on current track
        return (this.player.isPlaying() || this.player.isBuffering()) && this.player.cued(this.track);
    }
}
