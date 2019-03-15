import {AfterViewInit, Component, ElementRef, NgZone, OnDestroy, ViewChild, ViewEncapsulation} from '@angular/core';
import {FormattedDuration} from "../../formatted-duration.service";
import {Player} from "../../player.service";
import {Subscription} from "rxjs/Subscription";

@Component({
  selector: 'player-seekbar',
  templateUrl: './player-seekbar.component.html',
  styleUrls: ['./player-seekbar.component.scss'],
  encapsulation: ViewEncapsulation.None
})
export class PlayerSeekbarComponent implements AfterViewInit, OnDestroy {
    @ViewChild('outerTrack') private outerTrack: ElementRef;
    @ViewChild('progressTrack') private progressTrack: ElementRef;
    @ViewChild('progressHandle') private progressHandle: ElementRef;
    @ViewChild('elapsedTimeEl') private elapsedTimeEl: ElementRef;
    @ViewChild('trackLengthEl') private trackLengthEl: ElementRef;

    /**
     * Seekbar interval timer.
     */
    private seekbarInterval;

    /**
     * Formatted time that has elapsed since playback start.
     */
    public elapsedTime: string = '0:00';

    /**
     * Total length of currently loaded track.
     */
    public trackLength: number = 0;

    private subscriptions: Subscription[] = [];

    private cache: {
        outerTrackRect?: ClientRect,
        handleRect?: ClientRect,
        handlePercent?: number,
        handleEl?: HTMLElement,
        progressTrackEl?: HTMLElement
    } = {};

    /**
     * SeekViaDragDirective Constructor.
     */
    constructor(
        private el: ElementRef,
        private duration: FormattedDuration,
        private player: Player,
        private zone: NgZone
    ) {}

    /**
     * Called after component's view has been fully initialized.
     */
    ngAfterViewInit() {
        //wait for animations to complete
        //TODO: refactor this to use events instead
        setTimeout(() => {
            this.setupCache();
            this.bindHammerEvents();
            this.bindToPlayerStateEvents();
        }, 201);
    }

    /**
     * Called when component is destroyed.
     */
    ngOnDestroy() {
        this.stopSeekbarInterval();
        this.subscriptions.forEach(subscription => {
            subscription.unsubscribe();
        });
        this.subscriptions = [];
    }

    /**
     * Cache sizes required for positioning seekbar progress and handle.
     */
    private setupCache() {
        this.cache.outerTrackRect = this.outerTrack.nativeElement.getBoundingClientRect();
        this.cache.handleRect = this.progressHandle.nativeElement.getBoundingClientRect();
        this.cache.handlePercent = (this.cache.handleRect.width / this.cache.outerTrackRect.width) * 100 / 2;
        this.cache.handleEl = this.progressHandle.nativeElement;
        this.cache.progressTrackEl = this.progressTrack.nativeElement;
    }


    /**
     * Set time that has elapsed since playback start.
     */
    private setElapsedTime(time: number = null) {
        if (time === null) time = this.player.getCurrentTime();
        this.elapsedTime = time ? this.duration.fromSeconds(time) : '0:00';
        this.elapsedTimeEl.nativeElement.textContent = this.elapsedTime;
    }

    /**
     * Set total length of currently loaded track.
     */
    private setTrackLength() {
        const duration = this.player.getDuration();
        if (duration === this.trackLength) return;
        this.trackLength = duration;
        const formatted = duration ? this.duration.fromSeconds(this.trackLength) : '0:00';
        this.trackLengthEl.nativeElement.textContent = formatted;
    }

    /**
     * Seek player to specified point and update seekbar progress.
     */
    public seek(clickX: number) {
        if ( ! this.player.cued()) return;

        this.stopSeekbarInterval();

        const rect = this.cache.outerTrackRect;

        let ratio   = (clickX - rect.left) / rect.width,
            percent = ratio*100;

        if (percent > 100) return;

        this.positionElapsedTrackAndHandle(percent);

        return ratio * this.player.getDuration();
    }

    /**
     * Start seekbar progress interval.
     */
    public startSeekbarInterval() {
        this.stopSeekbarInterval();

        this.zone.runOutsideAngular(() => {
            this.seekbarInterval = setInterval(() => {
                let percent = (this.player.getCurrentTime() / this.player.getDuration()) * 100;

                if (isNaN(percent)) percent = 0;

                if (percent <= 0) return;

                this.positionElapsedTrackAndHandle(percent);

                this.setElapsedTime();
                this.setTrackLength();
            }, 50);
        })
    }

    /**
     * Stop seekbar progress interval.
     */
    public stopSeekbarInterval() {
        if ( ! this.seekbarInterval) return;

        clearInterval(this.seekbarInterval);
        this.seekbarInterval = null;
    }

    /**
     * Position seekbar progress bar and handle based on specified point.
     */
    private positionElapsedTrackAndHandle(leftPercent: number) {
        let handleLeft = leftPercent - this.cache.handlePercent;

        if (leftPercent < 0) leftPercent = 0;
        if (handleLeft < 0) handleLeft = 0;

        this.cache.handleEl.style.left = handleLeft+'%';
        this.cache.progressTrackEl.style.width = leftPercent+'%';
    }

    /**
     * Bind handlers to needed hammer.js events.
     */
    private bindHammerEvents() {
        let hammer, tap, pan;

        this.zone.runOutsideAngular(() => {
            hammer = new Hammer.Manager(this.el.nativeElement);
            tap = new Hammer.Tap();
            pan = new Hammer.Pan();
            hammer.add([tap, pan]);
        });

        hammer.on('tap panend', e => {
            if ( ! this.player.cued()) return;
            const time = this.seek(e.center.x);
            this.player.seekTo(time).then(() => {
                this.setElapsedTime(time);
                this.startSeekbarInterval();
            });
        });

        hammer.on("panstart", e => this.stopSeekbarInterval());
        hammer.on("panleft panright", e => this.seek(e.center.x));
    }

    /**
     * Reset seekbar to initial state.
     */
    private resetSeekbar() {
        this.positionElapsedTrackAndHandle(0);
        this.setElapsedTime(0);
    }

    /**
     * Update player controls on player state events.
     */
    private bindToPlayerStateEvents() {
        if (this.player.state.playing) {
            this.startSeekbarInterval();
        }

        const sub = this.player.state.onChange$.subscribe(type => {
            switch (type) {
                case 'BUFFERING_STARTED':
                    this.stopSeekbarInterval();
                    break;
                case 'PLAYBACK_STARTED':
                    this.startSeekbarInterval();
                    break;
                case 'PLAYBACK_PAUSED':
                    this.stopSeekbarInterval();
                    break;
                case 'PLAYBACK_STOPPED':
                    this.stopSeekbarInterval();
                    this.resetSeekbar();
                    break;
            }
        });

        this.subscriptions.push(sub);
    }
}
