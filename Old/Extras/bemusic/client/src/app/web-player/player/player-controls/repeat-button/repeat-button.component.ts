import {Component, ViewEncapsulation} from '@angular/core';
import {Player} from "../../player.service";

@Component({
    selector: 'repeat-button',
    templateUrl: './repeat-button.component.html',
    styleUrls: ['./repeat-button.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class RepeatButtonComponent {
    constructor(public player: Player) {}
}
