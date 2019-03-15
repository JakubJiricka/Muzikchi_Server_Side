import {
    AfterViewInit, Component, ElementRef, NgZone, OnDestroy, Renderer2, ViewChild,
    ViewEncapsulation
} from '@angular/core';
import {Player} from "../../player.service";
import 'hammerjs';
import {Subscription} from "rxjs/Subscription";
import {BrowserEvents} from "../../../../shared/browser-events.service";

@Component({
    selector: 'volume-controls',
    templateUrl: './volume-controls.component.html',
    styleUrls: ['./volume-controls.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class VolumeControlsComponent implements AfterViewInit, OnDestroy {
    @ViewChild('progressTrack') private progressTrack: ElementRef;
    @ViewChild('progressHandle') private progressHandle: ElementRef;
    @ViewChild('outerTrack') private outerTrack: ElementRef;

    /**
     * Active component subscriptions.
     */
    private subscriptions: Subscription[] = [];

    /**
     * Cache needed to position progress handle and bar.
     */
    private cache: {
        mainVolumeRect?: ClientRect,
        handleRect?: ClientRect,
        handlePercent?: number,
    } = {};

    /**
     * VolumeControlsComponent Constructor.
     */
    constructor(
        public player: Player,
        private renderer: Renderer2,
        private zone: NgZone,
        private browserEvents: BrowserEvents
    ) {}

    /**
     * Called after component's view has been fully initialized.
     */
    ngAfterViewInit() {
        this.bindHammerEvents();
        this.cacheNodes();
        this.setInitialVolume();
        this.initKeybinds();
    }

    /**
     * Called when a component is destroyed.
     */
    ngOnDestroy() {
        this.subscriptions.forEach(subscription => {
            subscription.unsubscribe();
        });

        this.subscriptions = [];
    }

    /**
     * Cache nodes needed to position volume handle and progress bar.
     */
    private cacheNodes() {
        this.showHandle();
        this.cache.mainVolumeRect = this.outerTrack.nativeElement.getBoundingClientRect();
        this.cache.handleRect = this.progressHandle.nativeElement.getBoundingClientRect();
        this.cache.handlePercent = (this.cache.handleRect.width / this.cache.mainVolumeRect.width) * 100 / 2;
        this.hideHandle();
    }

    /**
     * Set volume from click x coordinate.
     */
    private setVolumeFromCoordinate(clickX: number) {
        let volume = ((clickX - this.cache.mainVolumeRect.left) / this.cache.mainVolumeRect.width) * 100;
        this.setVolume(volume);
        if (volume > 0) this.player.unMute();
    }

    /**
     * Set initial player volume.
     */
    private setInitialVolume() {
        let ratio = ((this.player.getVolume()/100)*100);
        this.positionTrackAndHandle(ratio);
    }

    /**
     * Set player volume based on specified percentage;
     */
    public setVolume(percentage: number) {
        if (percentage > 100) return;

        if (percentage <= 0) {
            return this.player.mute();
        }

        if (this.player.isMuted() && percentage > 0) {
            this.player.unMute();
        }

        this.player.setVolume(percentage);
        this.positionTrackAndHandle(percentage);
    }

    /**
     * Position volume track and handle.
     */
    private positionTrackAndHandle(percentage) {
        if (percentage > 100 || percentage <= 0) return;

        this.progressTrack.nativeElement.style.width = percentage+'%';
        this.progressHandle.nativeElement.style.left = percentage - this.cache.handlePercent+'%';
    }

    /**
     * Show volume bar handle.
     */
    private showHandle() {
        this.renderer.setStyle(this.progressHandle.nativeElement, 'display', 'block');
    }

    /**
     * Hide volume bar handle.
     */
    private hideHandle() {
        this.renderer.setStyle(this.progressHandle.nativeElement, 'display', '');
    }

    /**
     * Bind handlers to needed hammer.js events.
     */
    private bindHammerEvents() {
        let hammer, tap, pan;

        this.zone.runOutsideAngular(() => {
            hammer = new Hammer.Manager(this.outerTrack.nativeElement);
            tap = new Hammer.Tap(); pan = new Hammer.Pan();
            hammer.add([tap, pan]);
        });

        //prevent handle from disappearing, if user hovers out of player
        //controls bounds, but have not released mouse button yet
        hammer.on('panstart', e => this.showHandle());
        hammer.on('panend', e => this.hideHandle());

        hammer.on('tap panleft panright', e => this.setVolumeFromCoordinate(e.center.x));
    }

    /**
     * Initiate volume keyboard shortcuts.
     */
    private initKeybinds() {
        const sub = this.browserEvents.globalKeyDown$.subscribe((e: KeyboardEvent) => {
            //ctrl+shift+up - max volume
            if (e.ctrlKey && e.shiftKey && e.keyCode === this.browserEvents.keyCodes.arrowUp) {
                this.setVolume(100); e.preventDefault();
            }

            //ctrl+shift+down - mute player
            else if (e.ctrlKey && e.shiftKey && e.keyCode === this.browserEvents.keyCodes.arrowDown) {
                this.setVolume(0); e.preventDefault();
            }

            //ctrl+up - increase volume by 5
            else if (e.ctrlKey && e.keyCode === this.browserEvents.keyCodes.arrowUp) {
                this.setVolume(this.player.getVolume()+5); e.preventDefault();
            }

            //ctrl+down - reduce volume by 5
            else if (e.ctrlKey && e.keyCode === this.browserEvents.keyCodes.arrowDown) {
                this.setVolume(this.player.getVolume()-5); e.preventDefault();
            }
        });

        this.subscriptions.push(sub);
    }
}
