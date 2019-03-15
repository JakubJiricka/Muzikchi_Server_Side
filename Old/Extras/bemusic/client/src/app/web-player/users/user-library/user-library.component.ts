import {Component, ViewEncapsulation} from '@angular/core';
import {WebPlayerState} from "../../web-player-state.service";

@Component({
    selector: 'user-library',
    templateUrl: './user-library.component.html',
    styleUrls: ['./user-library.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class UserLibraryComponent {

    constructor(public state: WebPlayerState) {}
}
