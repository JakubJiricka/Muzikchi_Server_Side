import {PlaybackStrategy} from "./playback-strategy.interface";
import {PlayerState} from "../player-state.service";
import {Track} from "../../../shared/types/models/Track";
import {Injectable, NgZone} from "@angular/core";
import {PlayerQueue} from "../player-queue.service";
import {utils} from "../../../shared/utils";
import {Search} from "../../search/search.service";

declare const SC;

@Injectable()
export class SoundcloudStrategy implements PlaybackStrategy {

    /**
     * Whether player is already bootstrapped.
     */
    private bootstrapped = false;

    /**
     * Volume that should be set after player is bootstrapped.
     */
    private pendingVolume: number = null;

    /**
     * Html5 video element instance.
     */
    private soundcloud: any;

    /**
     * Track currently cued for playback.
     */
    private cuedTrack: Track;

    /**
     * Track that is currently being cued.
     */
    private cueing: Track;

    /**
     * Loaded track duration in seconds.
     */
    private duration = 0;

    /**
     * Elapsed time in seconds since the track started playing.
     */
    private position = 0;

    /**
     * Html5Strategy Constructor.
     */
    constructor(
        private state: PlayerState,
        private zone: NgZone,
        private queue: PlayerQueue,
        private utils: utils,
        private search: Search,
    ) {}

    /**
     * Start playback.
     */
    public async play() {
        await this.cueTrack(this.queue.getCurrent());
        this.soundcloud.play();
        this.state.playing = true;
    }

    /**
     * Pause playback.
     */
    public pause() {
        this.soundcloud.pause();
        this.state.playing = false;
    }

    /**
     * Stop playback.
     */
    public stop() {
        this.pause();
        this.seekTo(0);
        this.state.playing = false;
    }

    /**
     * Seek to specified time in track.
     */
    public seekTo(time: number) {
        this.soundcloud.seekTo(time * 1000);
    }

    /**
     * Get loaded track duration in seconds.
     */
    public getDuration() {
        return this.duration / 1000
    }

    /**
     * Get elapsed time in seconds since the track started playing.
     */
    public getCurrentTime() {
        return this.position / 1000;
    }

    /**
     * Set soundcloud player volume.
     */
    public setVolume(number: number) {
        if ( ! this.soundcloud) {
            this.pendingVolume = number / 100;
        } else {
            this.soundcloud.volume = number / 100;
        }
    }

    /**
     * Mute the player.
     */
    public mute() {
        this.pendingVolume = this.soundcloud.getVolume();
        this.soundcloud.setVolume(0);
    }

    /**
     * Unmute the player.
     */
    public unMute() {
        let volume = this.pendingVolume || 50;
        this.soundcloud.setVolume(volume);
    }

    /**
     * Get track that is currently cued for playback.
     */
    public getCuedTrack(): Track {
        return this.cuedTrack;
    }

    /**
     * Check if youtube player is ready.
     */
    public ready() {
        return this.bootstrapped;
    }

    /**
     * Fetch youtube ID for specified track if needed and cue it in youtube player.
     */
    public async cueTrack(track: Track): Promise<any> {
        if (this.cueing === track || this.cuedTrack === track) return;

        this.cueing = track;

        this.state.buffering = true;

        if ( ! track.youtube_id) {
            const artist = utils.getObjectProp(track, 'album.artist.name') || track.artists[0];
            let results = await this.search.videoId(artist, track.name).toPromise();
            track.youtube_id = results[0].id;
        }

        return new Promise(resolve => {
            this.bootstrap(track).then(() => {
                this.soundcloud.load(track.youtube_id, this.getPlayerVars(resolve));
                this.cuedTrack = track;
                this.cueing = null;
            });
        });
    }

    /**
     * Destroy soundcloud playback strategy.
     */
    public destroy() {
        this.soundcloud && this.soundcloud.remove && this.soundcloud.remove();
        this.soundcloud = null;
        this.bootstrapped = false;
        this.cuedTrack = null;
    }

    /**
     * Bootstrap soundcloud player.
     */
    private bootstrap(track: Track): Promise<any> {
        if (this.bootstrapped) return new Promise(resolve => resolve());

        return new Promise(resolve => {
            this.utils.loadScript('https://w.soundcloud.com/player/api.js').then(() => {
                const iframe = document.createElement('iframe');

                iframe.onload = () => {
                    this.soundcloud = SC.Widget(iframe);
                    this.handlePlayerStateChangeEvents();
                    resolve();
                };

                iframe.id = 'soundcloud-iframe';
                iframe.src = 'https://w.soundcloud.com/player/?url='+track.youtube_id+'&color=0066cc';
                document.querySelector('.soundcloud-player').appendChild(iframe);
            });
        });
    }

    /**
     * Handle soundcloud playback state change events.
     */
    private handlePlayerStateChangeEvents() {
        this.soundcloud.bind(SC.Widget.Events.PLAY, () => {
            this.setState('playing', true);
        });

        this.soundcloud.bind(SC.Widget.Events.PAUSE, () => {
            this.setState('playing', false);
        });

        this.soundcloud.bind(SC.Widget.Events.PLAY_PROGRESS, (e) => {
            this.position = e.currentPosition;
        });

        this.soundcloud.bind(SC.Widget.Events.ERROR, () => {
            this.cuedTrack = null;
            this.setState('playing', false);
            this.state.firePlaybackEnded();
        });

        this.soundcloud.bind(SC.Widget.Events.FINISH, () => {
            this.state.firePlaybackEnded();
            this.setState('playing', false);
        });
    }

    /**
     * Set specified player state.
     */
    private setState(name: string, value: boolean) {
        this.zone.run(() => this.state[name] = value);
    }

    /**
     * Handle soundcloud player ready event.
     */
    private handlePlayerReadyEvent(resolve?) {
        if (this.state.muted) this.mute();
        this.bootstrapped = true;
        resolve && resolve();
        this.state.fireReadyEvent();

        this.soundcloud.getDuration(duration => {
            this.duration = duration;
        });

        if (this.pendingVolume) {
            this.setVolume(this.pendingVolume);
            this.pendingVolume = null;
        }
    }

    /**
     * Get soundcloud player options.
     */
    private getPlayerVars(resolve) {
        return {
            buying: false,
            liking: false,
            download: false,
            sharing: false,
            show_artwork: false,
            show_comments: false,
            show_playcount: false,
            show_user: false,
            callback: () => {
                this.handlePlayerReadyEvent(resolve);
            }
        }
    }
}