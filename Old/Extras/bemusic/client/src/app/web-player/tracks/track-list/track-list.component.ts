import {Component, ElementRef, EventEmitter, Input, NgZone, OnChanges, OnDestroy, OnInit, Output, QueryList, ViewChildren, ViewEncapsulation} from '@angular/core';
import {Track} from "../../../shared/types/models/Track";
import {Player} from "../../player/player.service";
import {FormattedDuration} from "../../player/formatted-duration.service";
import {Album} from "../../../shared/types/models/Album";
import {WpUtils} from "../../web-player-utils";
import {WebPlayerUrls} from "../../web-player-urls.service";
import {ContextMenu} from "../../context-menu/context-menu.service";
import {TrackContextMenuComponent} from "../track-context-menu/track-context-menu.component";
import {PlaylistTrackContextMenuComponent} from "../../playlists/playlist-track-context-menu/playlist-track-context-menu.component";
import {SelectedTracks} from "./selected-tracks.service";
import {BrowserEvents} from "../../../shared/browser-events.service";
import {Subscription} from "rxjs/Subscription";
import {WebPlayerState} from "../../web-player-state.service";

@Component({
    selector: 'track-list',
    templateUrl: './track-list.component.html',
    styleUrls: ['./track-list.component.scss'],
    providers: [SelectedTracks],
    encapsulation: ViewEncapsulation.None,
})
export class TrackListComponent implements OnInit, OnChanges, OnDestroy {
    @ViewChildren('trackListRow') trackListRows: QueryList<ElementRef>;

    /**
     * Active component subscriptions.
     */
    protected subscriptions: Subscription[] = [];

    /**
     * Tracks to render.
     */
    @Input() tracks: Track[] = [];

    /**
     * Album tracks belong to.
     */
    @Input() album: Album = new Album();

    /**
     * Whether artist column should be displayed.
     */
    @Input() showArtist = false;

    /**
     * Whether album column should be displayed.
     */
    @Input() showAlbum = false;

    /**
     * Whether popularity column should be displayed.
     */
    @Input() showPopularity = true;

    /**
     * Whether added at column should be displayed.
     */
    @Input() showAddedAt = false;

    /**
     * Whether header should be displayed.
     */
    @Input() showHeader = true;

    /**
     * Whether player should be set to handle continuous
     * playback when track is played from this track list.
     */
    @Input() handleContinuousPlayback = true;

    /**
     * Id of item that specified tracks belong to.
     */
    @Input() queueItemId: string|number;

    /**
     * Params for context menu that should be opened for tracks.
     */
    @Input() contextMenuParams = {type: 'track', extra: {}};

    /**
     * Track to select on track list init.
     */
    @Input() select: Track;

    /**
     * Fired when delete button is pressed when tracks are selected.
     */
    @Output() delete = new EventEmitter();

    /**
     * Fired when play button is pressed.
     */
    @Output() play = new EventEmitter();

    /**
     * TrackListComponent Constructor.
     */
    constructor(
        public player: Player,
        private duration: FormattedDuration,
        public urls: WebPlayerUrls,
        private contextMenu: ContextMenu,
        private zone: NgZone,
        private el: ElementRef,
        public selectedTracks: SelectedTracks,
        private browserEvents: BrowserEvents,
        public state: WebPlayerState,
    ) {}

    ngOnInit() {
        this.bindHammerEvents();
        this.bindKeyboardShortcuts();

        if (this.select) {
            this.selectedTracks.add(this.select);
        }
    }

    ngOnChanges() {
        this.formatTrackDurations();
    }

    ngOnDestroy() {
        this.subscriptions.forEach(subscription => {
            subscription.unsubscribe();
        });
        this.subscriptions = [];
    }

    /**
     * Check if specified track is cued and playing.
     */
    public trackIsPlaying(track: Track) {
        return this.player.isPlaying() && this.player.cued(track);
    }

    /**
     * Add tracks from specified index to queue and start playback.
     */
    public playTrack(track: Track, index: number) {
        this.play.emit(track);

        //if there's an observer for play event, bail
        //and let the observer handle playback
        if (this.play.observers.length) return;

        this.player.handleContinuousPlayback = this.handleContinuousPlayback;

        if (this.player.cued(track)) {
            this.player.play();
        } else {
            this.playFrom(index);
        }
    }

    /**
     * Play or pause specified track.
     */
    public async toggleTrackPlayback(track: Track, index: number) {
        if (this.trackIsPlaying(track)) {
            this.player.pause();
        } else {
            this.playTrack(track, index);
        }
    }

    /**
     * Get formatted track popularity value.
     */
    public getTrackPopularity(track: Track) {
        if ( ! track.spotify_popularity) {
            track.spotify_popularity = 50;
        }

        return track.spotify_popularity + '%';
    }

    /**
     * Show context menu for specified track.
     */
    public showContextMenu(track: Track, e: MouseEvent) {
        e.stopPropagation();
        e.preventDefault();
        this.contextMenu.show(this.getContextMenuComponent(), this.getContextMenuParams(track), e.target);
    }

    /**
     * Get params needed to open context menu for track.
     */
    public getContextMenuParams(track: Track) {
        return Object.assign(
            {item: track, type: this.contextMenuParams.type, selectedTracks: this.selectedTracks},
            this.contextMenuParams.extra
        );
    }

    /**
     * Get context menu component based on specified type.
     */
    private getContextMenuComponent() {
        if (this.contextMenuParams.type === 'playlist-track') {
            return PlaylistTrackContextMenuComponent;
        } else {
            return TrackContextMenuComponent;
        }
    }

    /**
     * Add tracks from specified index to player queue and start playback.
     */
    private playFrom(index: number) {
        let tracks = this.tracks.slice(index, this.tracks.length);
        tracks = WpUtils.assignAlbumToTracks(tracks, this.album);

        this.player.overrideQueue({tracks, queuedItemId: this.queueItemId}).then(() => {
            this.player.play();
        });
    }

    /**
     * Format duration of every track to human readable format.
     */
    private formatTrackDurations() {
        this.tracks.forEach(track => {
            track['formatted_duration'] = this.duration.fromMilliseconds(track.duration);
        });
    }

    /**
     * Bind handlers to needed hammer.js events.
     */
    private bindHammerEvents() {
        let hammer, singleTap, doubleTap;

        this.zone.runOutsideAngular(() => {
            hammer = new Hammer.Manager(this.el.nativeElement);
            singleTap = new Hammer.Tap({event: 'singletap'});
            doubleTap = new Hammer.Tap({event: 'doubletap', taps: 2});
            hammer.add([doubleTap, singleTap]);
        });

        //select track on tap or multiple tracks via when ctrl is pressed
        hammer.on('singletap', e => {
            this.zone.run(() => {
                let data = this.getTrackFromEvent(e);

                if ( ! data) return;

                if ( ! e.srcEvent.ctrlKey) {
                    this.selectedTracks.clear();
                    this.selectedTracks.add(data.track);
                } else {
                    this.selectedTracks.toggle(data.track);
                }
            });
        });

        //play track on double tap
        hammer.on('doubletap', e => {
            this.zone.run(() => {
                let data = this.getTrackFromEvent(e);
                if ( ! data) return;
                this.playTrack(data.track, data.index);
            });
        });

        //deselect all tracks when clicked outside of track list.
        const sub = this.browserEvents.globalClick$.subscribe(e => {
            if ( ! e.target.closest('.track-list-row')) {
                this.selectedTracks.clear();
            }
        });

        this.subscriptions.push(sub);
    }

    /**
     * Get track from specified hammer tap event.
     */
    private getTrackFromEvent(e: HammerInput): {track: Track, index: number} {
        if ( ! e.target) return;
        const row = e.target.closest('.track-list-item');
        if ( ! row) return;
        const id = parseInt(row.getAttribute('data-id'));
        const i = this.tracks.findIndex(track => track.id === id);
        return {track: this.tracks[i], index: i};
    }

    /**
     * Initiate tracks list shortcuts.
     */
    private bindKeyboardShortcuts() {
        const sub = this.browserEvents.globalKeyDown$.subscribe((e: KeyboardEvent) => {
            //ctrl+a - select all tracks
            if (e.ctrlKey && e.keyCode === this.browserEvents.keyCodes.letters.a) {
                this.tracks.forEach(track => this.selectedTracks.add(track));
                e.preventDefault();
            }

            //delete - fire delete event
            else if (e.keyCode === this.browserEvents.keyCodes.delete && ! this.selectedTracks.empty()) {
                this.delete.emit(this.selectedTracks.all());
                this.selectedTracks.clear();
                e.preventDefault();
            }
        });

        this.subscriptions.push(sub);
    }
}
