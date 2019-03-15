import {Injectable} from '@angular/core';

@Injectable()
export class WebPlayerState {

    /**
     * Whether web player is currently loading (globally)
     */
    public loading: boolean = false;

    /**
     * Whether mobile layout should be activated.
     */
    public isMobile: boolean = false;

    constructor() {
       this.isMobile = window.matchMedia && window.matchMedia('(max-width: 768px)').matches;
    }
}
