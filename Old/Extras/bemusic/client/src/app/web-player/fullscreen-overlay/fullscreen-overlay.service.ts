import { Injectable } from '@angular/core';
import {NavigationStart, Router} from "@angular/router";
import {Subscription} from "rxjs/Subscription";
import {Observable} from "rxjs/Observable";

@Injectable()
export class FullscreenOverlay {

    /**
     * Whether fullscreen overlay is currently maximized.
     */
    private maximized = false;

    /**
     * Currently active fullscreen overlay panel.
     */
    private activePanel: 'queue'|'video' = 'video';

    /**
     * Active service subscriptions.
     */
    protected subscriptions: Subscription[] = [];

    /**
     * FullscreenOverlay Constructor.
     */
    constructor(private router: Router) {}

    /**
     * Init fullscreen overlay service.
     */
    public init() {
        this.bindToRouterEvents();
    }

    /**
     * Check if fullscreen overlay is currently maximized.
     */
    public isMaximized(): boolean {
        return this.maximized;
    }

    /**
     * Minimize fullscreen overlay.
     */
    public minimize() {
        this.maximized = false;
        this.openVideoPanel();
    }

    /**
     * Maximize fullscreen overlay.
     */
    public maximize(): Promise<boolean> {
        this.maximized = true;

        //wait for animation to complete
        return new Promise(resolve => {
            setTimeout(() => {resolve()}, 201);
        });
    }

    public openQueuePanel() {
        this.activePanel = 'queue';
    }

    public openVideoPanel() {
        this.activePanel = 'video';
    }

    public activePanelIs(name: string) {
        return this.activePanel === name;
    }

    /**
     * Destroy fullscreen overlay service.
     */
    public destroy() {
        this.subscriptions.forEach(subscription => {
            subscription.unsubscribe();
        });
    }

    /**
     * Minimize fullscreen overlay when navigation occurs.
     */
    private bindToRouterEvents() {
        const sub = this.router.events
            .filter(e => e instanceof NavigationStart)
            .subscribe(() => this.minimize());

        this.subscriptions.push(sub);
    }

}
