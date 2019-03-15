import {EventEmitter, Injectable} from '@angular/core';
import {LocalStorage} from "../../shared/local-storage.service";

@Injectable()
export class PlayerState {

    private _playing = false;
    private _buffering = false;
    private _muted = false;
    private _repeating = true;
    private _repeatingOne = false;
    private _shuffling = false;

    public onChange$: EventEmitter<string> = new EventEmitter();

    /**
     * PlayerState Constructor.
     */
    constructor(private storage: LocalStorage) {}

    /**
     * Fired when playback ends (track reaches the end)
     */
    public firePlaybackEnded() {
        this.onChange$.emit('PLAYBACK_ENDED');
    }

    /**
     * Fired when playback is stopped (paused and seeked to 0)
     */
    public firePlaybackStopped() {
        this.onChange$.emit('PLAYBACK_STOPPED');
    }

    /**
     * Fired when playback strategy is first bootstrapped.
     */
    public fireReadyEvent() {
        this.onChange$.emit('PLAYBACK_STRATEGY_READY');
    }

    get playing(): boolean {
        return this._playing;
    }

    set playing(value: boolean) {
        if (this._playing === value) return;

        this._playing = value;

        if (value && this.buffering) this.buffering = false;

        this.onChange$.emit('PLAYBACK_' + (value ? 'STARTED' : 'PAUSED'));
    }

    get buffering(): boolean {
        return this._buffering;
    }

    set buffering(value: boolean) {
        if (this._buffering === value) return;

        this._buffering = value;

        if (value && this.playing) this.playing = false;

        this.onChange$.emit('BUFFERING_' + (value ? 'STARTED' : 'STOPPED'));
    }

    get muted(): boolean {
        return this._muted;
    }

    set muted(value: boolean) {
        this._muted = value;
        this.storage.set('player.muted', value);
    }

    get repeating(): boolean {
        return this._repeating;
    }

    set repeating(value: boolean) {
        this._repeating = value;
        this.storage.set('player.repeating', value);
    }

    get repeatingOne(): boolean {
        return this._repeatingOne;
    }

    set repeatingOne(value: boolean) {
        this._repeatingOne = value;
        if (value) this.repeating = false;
        this.storage.set('player.repeatingOne', value);
    }

    get shuffling(): boolean {
        return this._shuffling;
    }

    set shuffling(value: boolean) {
        this._shuffling = value;
        this.storage.set('player.shuffling', value);
    }
}
