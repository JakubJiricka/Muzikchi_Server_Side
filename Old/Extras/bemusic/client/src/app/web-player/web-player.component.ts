import {Component, OnDestroy, OnInit, Renderer2, ViewEncapsulation} from '@angular/core';
import {SearchSlideoutPanel} from "./search/search-slideout-panel/search-slideout-panel.service";
import {Player} from "./player/player.service";
import {ContextMenu} from "./context-menu/context-menu.service";
import {WebPlayerState} from "./web-player-state.service";
import {FullscreenOverlay} from "./fullscreen-overlay/fullscreen-overlay.service";
import {Settings} from "../shared/settings.service";

@Component({
    selector: 'web-player',
    templateUrl: './web-player.component.html',
    styleUrls: ['./web-player.component.scss'],
    encapsulation: ViewEncapsulation.None,
    host: {'id': 'web-player'}
})
export class WebPlayerComponent implements OnInit, OnDestroy {

    /**
     * Whether small video should be hidden.
     */
    public shouldHideVideo = false;

    /**
     * WebPlayerComponent Constructor.
     */
    constructor(
        public searchPanel: SearchSlideoutPanel,
        public player: Player,
        private renderer: Renderer2,
        private contextMenu: ContextMenu,
        public state: WebPlayerState,
        private overlay: FullscreenOverlay,
        private settings: Settings,
    ) {}

    ngOnInit() {
        this.player.init();
        this.overlay.init();
        this.shouldHideVideo = this.settings.get('player.hide_video');

        //disable default context menus in web player
        this.renderer.listen('document', 'contextmenu', e => {
            e.preventDefault();
            this.contextMenu.close();
        });
    }

    ngOnDestroy() {
        this.player.destroy();
        this.overlay.destroy();
    }
}
