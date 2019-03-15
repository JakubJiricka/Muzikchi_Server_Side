import {Injectable, NgZone} from '@angular/core';
import {Router} from "@angular/router";
import {CurrentUser} from "../auth/current-user";
import {Settings} from "./settings.service";

@Injectable()
export class PreviewApp {

    /**
     * PreviewEvents Constructor.
     */
    constructor(
        public zone: NgZone,
        public router: Router,
        public currentUser: CurrentUser,
        public settings: Settings
    ) {}

    /**
     * Resolve angular ready promise if app is
     * running under appearance preview iframe.
     */
    public init() {
        if ( ! this.tokenIsValid()) return;

        //parent app is waiting for preview app to load
        if (window['angularReadyResolve']) {
            window['angularReadyResolve'](this);
        }

        //parent app is not loaded yet
        else {
            window['previewApp'] = this;
        }
    }

    /**
     * Check if CSRF token in iframe url is valid.
     */
    private tokenIsValid(): boolean {
        return window.location.search.indexOf('preview='+this.settings.csrfToken) > -1;
    }
}