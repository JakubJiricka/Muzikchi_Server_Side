import {Component, Input, ViewEncapsulation} from '@angular/core';
import {Track} from "../../../../shared/types/models/Track";
import {UserLibrary} from "../user-library.service";

@Component({
    selector: 'library-track-toggle-button',
    templateUrl: './library-track-toggle-button.component.html',
    styleUrls: ['./library-track-toggle-button.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class LibraryTrackToggleButtonComponent {

    @Input() track: Track;

    constructor(public library: UserLibrary) {}
}
