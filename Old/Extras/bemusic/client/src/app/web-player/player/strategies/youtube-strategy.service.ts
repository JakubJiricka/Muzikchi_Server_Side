import {Injectable, NgZone} from '@angular/core';
import {PlayerQueue} from "../player-queue.service";
import {utils} from "../../../shared/utils";
import {Track} from "../../../shared/types/models/Track";
import {Search} from "../../search/search.service";
import {PlayerState} from "../player-state.service";
import {PlaybackStrategy} from "./playback-strategy.interface";
import {Settings} from "../../../shared/settings.service";

@Injectable()
export class YoutubeStrategy implements PlaybackStrategy {

    /**
     * Whether player is already bootstrapped.
     */
    private bootstrapped = false;

    /**
     * Whether youtube player is bootstrapping currently.
     */
    private bootstrapping: Promise<any>;

    /**
     * Track that is currently being cued.
     */
    private cueing: Track;

    /**
     * Youtube player.
     */
    private youtube: YT.Player;

    /**
     * Track currently cued for playback.
     */
    private cuedTrack: Track;

    /**
     * Volume that should be set after youtube player is bootstrapped.
     */
    private pendingVolume: number|null = null;

    /**
     * Results of last youtube search for currently cued video.
     */
    private searchResults: {title: string, id: string}[] = [];

    /**
     * Number of tracks skipped in queue due to youtube iframe or search error.
     */
    private numberOfTracksSkipped = 0;

    /**
     * YoutubeStrategy Constructor.
     */
    constructor(
        private queue: PlayerQueue,
        private state: PlayerState,
        private utils: utils,
        private search: Search,
        private zone: NgZone,
        private settings: Settings,
    ) {}

    /**
     * Load current queue item and play youtube video.
     */
    public async play() {
        await this.cueTrack(this.queue.getCurrent());
        this.youtube.playVideo();
        this.state.playing = true;
    }

    /**
     * Pause youtube video.
     */
    public pause() {
        this.youtube.pauseVideo();
        this.state.playing = false;
    }

    /**
     * stop youtube video.
     */
    public stop() {
        this.youtube.stopVideo();
        this.state.playing = false;
    }

    /**
     * Seek to specified time in youtube video.
     */
    public seekTo(time: number) {
        this.youtube.seekTo(time, true);
    }

    /**
     * Get loaded youtube video duration in seconds.
     */
    public getDuration(): number {
        return this.youtube.getDuration() || 0;
    }

    /**
     * Get elapsed time in seconds since the video started playing
     */
    public getCurrentTime(): number {
        return this.youtube.getCurrentTime();
    }

    /**
     * Set youtube player volume.
     */
    public setVolume(number: number) {
        if ( ! this.youtube || ! this.youtube.setVolume) {
            this.pendingVolume = number;
        } else {
            this.youtube.setVolume(number);
        }
    }

    /**
     * Mute youtube player.
     */
    public mute() {
        this.youtube && this.youtube.mute && this.youtube.mute();
    }

    /**
     * Unmute youtube player.
     */
    public unMute() {
        this.youtube && this.youtube.unMute && this.youtube.unMute();
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

        //clear search results, so old search results are not used for new track
        this.searchResults = [];

        this.state.buffering = true;

        if ( ! track.youtube_id) {
            const artist = utils.getObjectProp(track, 'album.artist.name') || track.artists[0];
            this.searchResults = await this.search.videoId(artist, track.name).toPromise();
            this.assignFirstSearchResult(track);
        }

        return this.bootstrap(track.youtube_id).then(() => {
            this.cueYoutubeVideo(track);
        });
    }

    /**
     * Destroy youtube playback strategy.
     */
    public destroy() {
        this.youtube && this.youtube.destroy();
        this.youtube = null;
        this.bootstrapped = false;
        this.cuedTrack = null;
        this.searchResults = [];
    }

    /**
     * Set specified player state.
     */
    private setState(name: string, value: boolean) {
        this.zone.run(() => this.state[name] = value);
    }

    /**
     * Cue specified youtube video for specified track.
     */
    private cueYoutubeVideo(track: Track) {
        if (track.youtube_id !== this.getYoutubeId()) {
            const suggestedQuality = this.settings.get('youtube.suggested_quality');
            this.youtube.cueVideoById({videoId: track.youtube_id, suggestedQuality});
        }

        this.cuedTrack = track;
        this.cueing = null;
    }

    /**
     * Get currently cued youtube video ID .
     */
    private getYoutubeId(): string {
        let url = this.youtube.getVideoUrl();
        return url && url.split('v=')[1];
    }

    /**
     * Assign first search result youtube id to track and shift the search array.
     */
    private assignFirstSearchResult(track: Track) {
        if (this.searchResults && this.searchResults.length) {
            track.youtube_id = this.searchResults[0].id;
            this.searchResults.shift();
        }

        return track;
    }

    /**
     * Try to play fallback videos from last youtube search.
     */
    private tryToPlayFallbackVideos(e) {
        if ( ! this.searchResults.length) {
            return this.handleYoutubeError();
        }

        this.assignFirstSearchResult(this.cuedTrack);
        this.cueYoutubeVideo(this.cuedTrack);
        this.youtube.playVideo();
    }

    /**
     * Handle youtube search or iframe embed error.
     */
    private handleYoutubeError() {
        this.cuedTrack = null;
        this.setState('playing', false);
        this.numberOfTracksSkipped++;

        //if we have skipped more then 5 tracks, we can assume
        //that there's a critical issue with youtube search or
        //embed so we should stop the playback and bail.
        if (this.numberOfTracksSkipped <= 5) {
            this.state.firePlaybackEnded();
        }

        return;
    }

    /**
     * Bootstrap youtube playback strategy.
     */
    private bootstrap(videoId: string): Promise<any> {
        if (this.bootstrapped) return new Promise(resolve => resolve());
        if (this.bootstrapping) return this.bootstrapping;

        this.utils.loadScript('https://www.youtube.com/iframe_api');

        this.bootstrapping = new Promise(resolve => {
            if (window['onYouTubeIframeAPIReady']) {
                return this.initYoutubePlayer(videoId, resolve);
            } else {
                window['onYouTubeIframeAPIReady'] = () => {
                    this.initYoutubePlayer(videoId, resolve);
                }
            }
        });

        return this.bootstrapping;
    }

    /**
     * Initiate youtube iframe player.
     */
    private initYoutubePlayer(videoId: string, resolve) {
        this.youtube = new YT.Player('youtube-player', {
            videoId: videoId,
            playerVars: this.getPlayerVars(),
            events: {
                onReady: () => this.onPlayerReady(resolve),
                onError: this.tryToPlayFallbackVideos.bind(this),
                onStateChange: this.onYoutubePlayerStateChange.bind(this)
            }
        });
    }

    /**
     * Handle youtube player ready event.
     */
    private onPlayerReady(resolve) {
        if (this.state.muted) this.mute();
        this.bootstrapped = true;
        this.bootstrapping = null;
        resolve();
        this.state.fireReadyEvent();

        if (this.pendingVolume) {
            this.setVolume(this.pendingVolume);
            this.pendingVolume = null;
        }
    }

    /**
     * Handle youtube player state changes.
     */
    private onYoutubePlayerStateChange(e: YT.OnStateChangeEvent) {
        switch (e.data) {
            case YT.PlayerState.ENDED:
                this.state.firePlaybackEnded();
                this.setState('playing', false);
                break;
            case YT.PlayerState.PLAYING:
                this.numberOfTracksSkipped = 0;
                this.setState('playing', true);
                break;
            case YT.PlayerState.PAUSED:
                this.setState('playing', false);
                break;
        }
    }

    /**
     * Get youtube player vars.
     */
    private getPlayerVars(): YT.PlayerVars {
        return {
            autoplay: 0,
            rel: 0,
            showinfo: 0,
            disablekb: 1,
            fs: 0,
            controls: 0,
            modestbranding: 1,
            iv_load_policy: 3,
            playsinline: 1,
        }
    }
}
