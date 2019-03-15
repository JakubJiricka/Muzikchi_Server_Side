import {Component, ViewEncapsulation} from '@angular/core';
import {Player} from "../player.service";
import {FullscreenOverlay} from "../../fullscreen-overlay/fullscreen-overlay.service";

@Component({
    selector: 'mobile-player-controls',
    templateUrl: './mobile-player-controls.component.html',
    styleUrls: ['./mobile-player-controls.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class MobilePlayerControlsComponent {

    constructor(public player: Player, public overlay: FullscreenOverlay) {
    }
}
