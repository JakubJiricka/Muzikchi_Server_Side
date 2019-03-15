import {Injectable} from '@angular/core';
import {PlayerQueue} from "./player-queue.service";
import {Track} from "../../shared/types/models/Track";
import {YoutubeStrategy} from "./strategies/youtube-strategy.service";
import {PlayerState} from "./player-state.service";
import {LocalStorage} from "../../shared/local-storage.service";
import {Settings} from "../../shared/settings.service";
import {FullscreenOverlay} from "../fullscreen-overlay/fullscreen-overlay.service";
import {WebPlayerState} from "../web-player-state.service";
import {PlaybackStrategy} from "./strategies/playback-strategy.interface";
import {Html5Strategy} from "./strategies/html5-strategy.service";
import {SoundcloudStrategy} from "./strategies/soundcloud-strategy.service";
import {Subscription} from "rxjs/Subscription";
import {BrowserEvents} from "../../shared/browser-events.service";
import {TrackPlays} from "./track-plays.service";

@Injectable()
export class Player {

    /**
     * Active service subscriptions.
     */
    private subscriptions: Subscription[] = [];

    /**
     * Currently active playback strategy.
     */
    private playbackStrategy: PlaybackStrategy;

    /**
     * Currently active playback strategy.
     */
    private activePlaybackStrategy: 'youtube'|'html5'|'soundcloud';

    /**
     * Current player volume.
     */
    private volume: number;

    /**
     * Whether continuous playback should be
     * handled by the player after song ends.
     */
    public handleContinuousPlayback = true;

    /**
     * Whether playback has been started via user gesture.
     *
     * If true, there's no need to maximize player overlay
     * anymore, because external controls will work properly.
     */
    private playbackStartedViaGesture = false;

    /**
     * Player Constructor.
     */
    constructor(
        public queue: PlayerQueue,
        private youtube: YoutubeStrategy,
        private html5: Html5Strategy,
        private soundcloud: SoundcloudStrategy,
        private storage: LocalStorage,
        private settings: Settings,
        public state: PlayerState,
        private globalState: WebPlayerState,
        private overlay: FullscreenOverlay,
        private browserEvents: BrowserEvents,
        private trackPlays: TrackPlays
    ) {}

    /**
     * Start the playback.
     */
    public play() {
        if ( ! this.ready()) return;
        const track = this.queue.getCurrent();
        if ( ! track) return this.stop();
        this.setStrategy(track);
        this.maybeMaximizeOverlay();
        this.playbackStrategy.play();
    }

    /**
     * Pause the playback.
     */
    public pause() {
        this.playbackStrategy.pause();
    }

    /**
     * Play or pause player based on current playback state.
     */
    public togglePlayback() {
        if (this.isPlaying()) {
            this.pause();
        } else {
            this.play();
        }
    }

    /**
     * Check if current playback strategy is ready.
     */
    public ready() {
        return this.playbackStrategy.ready();
    }

    /**
     * Check if playback is in progress.
     */
    public isPlaying(): boolean {
        return this.state.playing;
    }

    /**
     * Check if player has any or specified track cued.
     */
    public cued(track?: Track) {
        const cued = this.getCuedTrack() && this.getCuedTrack().id;
        if ( ! track) return cued;
        return cued && this.getCuedTrack() === track;
    }

    /**
     * Get player state service.
     */
    public getState(): PlayerState {
        return this.state;
    }

    /**
     * Get player queue service.
     */
    public getQueue(): PlayerQueue {
        return this.queue;
    }

    /**
     * Check if player is buffering currently..
     */
    public isBuffering(): boolean {
        return this.state.buffering;
    }

    /**
     * Check if player is muted.
     */
    public isMuted(): boolean {
        return this.state.muted;
    }

    /**
     * Get track that is currently cued.
     */
    public getCuedTrack(): Track {
        if ( ! this.playbackStrategy) return null;
        return this.playbackStrategy.getCuedTrack();
    }

    /**
     * Mute player.
     */
    public mute() {
        this.playbackStrategy.mute();
        this.state.muted = true;
    }

    /**
     * Unmute player.
     */
    public unMute() {
        this.playbackStrategy.unMute();
        this.state.muted = false;
    }

    /**
     * Get current player volume.
     */
    public getVolume() {
        return this.volume;
    }

    /**
     * Set volume to a number between 0 and 100.
     */
    public setVolume(volume: number) {
        this.volume = volume;
        this.playbackStrategy.setVolume(volume);
        this.storage.set('player.volume', volume);
    }

    /**
     * Stop playback and seek to start of track.
     */
    public stop() {
        if ( ! this.state.playing) return;
        this.playbackStrategy.pause();
        this.seekTo(0);
        this.state.playing = false;
        this.state.firePlaybackStopped();
    }

    /**
     * Get time that has elapsed since playback start.
     */
    public getCurrentTime() {
        return this.playbackStrategy.getCurrentTime();
    }

    /**
     * Get total duration of track in seconds.
     */
    public getDuration() {
        return this.playbackStrategy.getDuration();
    }

    /**
     * Seek to specified time in track.
     */
    public seekTo(time: number): Promise<any> {
        this.playbackStrategy.seekTo(time);
        return new Promise(resolve => setTimeout(() => resolve(), 50));
    }

    /**
     * Toggle between repeat, repeat one and no repeat modes.
     */
    public toggleRepeatMode() {
        if (this.state.repeating) {
            this.state.repeatingOne = true;
        } else if (this.state.repeatingOne) {
            this.state.repeatingOne = false;
            this.state.repeating = false;
        } else {
            this.state.repeating = true;
        }
    }

    /**
     * Play next track in queue based on current repeat setting.
     */
    public playNext() {
        this.stop(); let track = this.queue.getCurrent();

        if (this.state.repeating && this.queue.isLast()) {
            track = this.queue.getFirst();
        } else if ( ! this.state.repeatingOne) {
            track = this.queue.getNext();
        }

        this.queue.select(track);
        this.play();
    }

    /**
     * Play previous track in queue based on current repeat setting.
     */
    public playPrevious() {
        this.stop(); let track = this.queue.getCurrent();

        if (this.state.repeating && this.queue.isFirst()) {
            track = this.queue.getLast();
        } else if (!this.state.repeatingOne) {
            track = this.queue.getPrevious();
        }

        this.queue.select(track);
        this.play();
    }

    /**
     * Toggle player shuffle mode.
     */
    public toggleShuffle() {
        if (this.state.shuffling) {
            this.queue.restoreOriginal();
        } else {
            this.queue.shuffle();
        }

        this.state.shuffling = !this.state.shuffling;
    }

    /**
     * Override player queue and cue first track.
     */
    public overrideQueue(params: {tracks: Track[], queuedItemId?: number|string}, queuePointer: number = 0): Promise<any> {
        this.putQueueIntoLocalStorage(params.tracks);
        this.queue.override(params, queuePointer);

        return this.cueTrack(this.queue.getCurrent());
    }

    /**
     * Cue specified track for playback.
     */
    public cueTrack(track: Track): Promise<any> {
        let promise: Promise<any>;
        this.setStrategy(track);

        if ( ! track || ! this.playbackStrategy) {
            promise = new Promise(resolve => resolve());
        } else {
            this.queue.select(track);
            promise = this.playbackStrategy.cueTrack(track);
        }

        return promise.then(() => {
            this.state.buffering = false;
        });
    }

    /**
     * Get currently active playback strategy.
     */
    public getPlaybackStrategy(): string {
        return this.activePlaybackStrategy;
    }

    /**
     * Init the player.
     */
    public init() {
        this.loadStateFromLocalStorage();
        this.setStrategy(this.queue.getCurrent());
        this.setInitialVolume();
        this.cueTrack(this.queue.getCurrent());
        this.bindToPlaybackStateEvents();
        this.initKeybinds();
    }

    /**
     * Destroy the player.
     */
    public destroy() {
        this.playbackStrategy && this.playbackStrategy.destroy();
        this.state.playing = false;

        this.subscriptions.forEach(subscription => {
            subscription.unsubscribe();
        });

        this.subscriptions = [];
    }

    /**
     * Put specified queue into local storage and limit tracks to 15.
     */
    private putQueueIntoLocalStorage(tracks: Track[]) {
        if ( ! tracks) return;
        this.storage.set('player.queue', {tracks: tracks.slice(0, 15)});
    }

    /**
     * Set playback strategy based on specified track.
     */
    private setStrategy(track: Track): PlaybackStrategy {
        if (track && track.url) {
            this.playbackStrategy = this.html5;
            this.activePlaybackStrategy = 'html5';
        } else if (this.settings.get('audio_search_provider') === 'soundcloud') {
            this.playbackStrategy = this.soundcloud;
            this.activePlaybackStrategy = 'soundcloud';
        } else {
            this.playbackStrategy = this.youtube;
            this.activePlaybackStrategy = 'youtube';
        }

        //destroy all except current active playback strategy
        if (this.activePlaybackStrategy !== 'youtube') this.youtube.destroy();
        if (this.activePlaybackStrategy !== 'html5') this.html5.destroy();
        if (this.activePlaybackStrategy !== 'soundcloud') this.soundcloud.destroy();

        return this.playbackStrategy;
    }

    private loadStateFromLocalStorage() {
        this.state.muted = this.storage.get('player.muted', false);
        this.state.repeating = this.storage.get('player.repeating', true);
        this.state.repeatingOne = this.storage.get('player.repeatingOne', false);
        this.state.shuffling = this.storage.get('player.shuffling', false);
        const queuePointer = this.storage.get('player.queue.pointer', 0);
        this.queue.override(this.storage.get('player.queue', {tracks: []}), queuePointer);
    }

    /**
     * Set initial player volume.
     */
    private setInitialVolume() {
        let defaultVolume = this.settings.get('player.default_volume', 30);
            defaultVolume = this.storage.get('player.volume', defaultVolume);

        this.setVolume(defaultVolume);
        this.html5.setVolume(defaultVolume);
    }

    /**
     * Maximize fullscreen overlay if we're on mobile,
     * because youtube embed needs to be visible to start
     * playback with external youtube iframe api controls
     */
    private async maybeMaximizeOverlay(): Promise<boolean> {
        const shouldOpen = this.settings.get('player.mobile.auto_open_overlay');

        if (this.playbackStartedViaGesture || ! shouldOpen || ! this.globalState.isMobile) return;

        await this.overlay.maximize();
        this.playbackStartedViaGesture = true;
    }

    /**
     * Play next track when current track ends.
     */
    private bindToPlaybackStateEvents() {
        this.state.onChange$.subscribe(type => {
            if (type === 'PLAYBACK_STARTED') {
                this.trackPlays.increment(this.getCuedTrack());
            } else if (type === 'PLAYBACK_ENDED' && this.handleContinuousPlayback) {
                this.trackPlays.clearPlayedTrack(this.getCuedTrack());
                this.playNext();
            }
        });
    }

    /**
     * Initiate player keyboard shortcuts.
     */
    public initKeybinds() {
        const sub = this.browserEvents.globalKeyDown$.subscribe((e: KeyboardEvent) => {
            //SPACE - toggle playback
            if (e.keyCode === this.browserEvents.keyCodes.space) {
                this.togglePlayback(); e.preventDefault();

                //ctrl+right - play next track
            } else if (e.ctrlKey && e.keyCode === this.browserEvents.keyCodes.arrowRight) {
                this.playNext(); e.preventDefault();
            }

            //ctrl+left - play previous track
            else if (e.ctrlKey && e.keyCode === this.browserEvents.keyCodes.arrowLeft) {
                this.playPrevious(); e.preventDefault();
            }
        });

        this.subscriptions.push(sub);
    }
}
