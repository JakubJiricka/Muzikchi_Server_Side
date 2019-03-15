import {Component, OnDestroy, OnInit, ViewEncapsulation} from '@angular/core';
import {SearchSlideoutPanel} from "./search-slideout-panel.service";
import {WebPlayerUrls} from "../../web-player-urls.service";
import {BrowserEvents} from "../../../shared/browser-events.service";
import {Subscription} from "rxjs/Subscription";
import {NavigationStart, Router} from "@angular/router";
import {Player} from "../../player/player.service";
import {Track} from "../../../shared/types/models/Track";

@Component({
    selector: 'search-slideout-panel',
    templateUrl: './search-slideout-panel.component.html',
    styleUrls: ['./search-slideout-panel.component.scss'],
    encapsulation: ViewEncapsulation.None,
    host: {'[class.open]': 'panel.isOpen'}
})
export class SearchSlideoutPanelComponent implements OnInit, OnDestroy {

    /**
     * Active component subscriptions.
     */
    private subscriptions: Subscription[] = [];

    /**
     * SearchSlideoutPanelComponent Constructor.
     */
    constructor(
        public panel: SearchSlideoutPanel,
        public urls: WebPlayerUrls,
        public browserEvents: BrowserEvents,
        private router: Router,
        private player: Player
    ) {}

    ngOnInit() {
        this.bindToRouter();
        this.bindToClickEvent();
    }

    ngOnDestroy() {
        this.subscriptions.forEach(subscription => {
            subscription.unsubscribe();
        });
        this.subscriptions = [];
    }

    /**
     * Play specified track.
     */
    public playTrack(track: Track) {
        this.player.queue.prepend([track]);
        this.player.cueTrack(track).then(() => {
            this.player.play();
        });
    }

    /**
     * Pause the player.
     */
    public pausePlayer() {
        this.player.pause();
    }

    /**
     * Go to specified track's page.
     */
    public goToTrackPage(track: Track) {
        this.router.navigate(this.urls.track(track));
    }

    /**
     * Close search panel when navigation to different route occurs.
     */
    private bindToRouter() {
        const sub = this.router.events
            .filter(e => e instanceof NavigationStart)
            .subscribe(() => this.panel.close());

        this.subscriptions.push(sub);
    }

    /**
     * Close search panel when clicking outside it.
     */
    private bindToClickEvent() {
        const sub = this.browserEvents.globalClick$.subscribe(e => {
            if ( ! this.panel.isOpen) return;

            const clickedInsidePanel = e.target.closest('search-slideout-panel .panel'),
                  clickedInsideInput = e.target.closest('.search-bar-container'),
                  clickedInsideControls = e.target.closest('player-controls');

            if ( ! clickedInsidePanel && ! clickedInsideInput && ! clickedInsideControls) {
                this.panel.close();
            }
        });

        this.subscriptions.push(sub);
    }
}
