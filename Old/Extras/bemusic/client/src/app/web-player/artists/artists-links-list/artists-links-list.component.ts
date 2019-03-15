import {ChangeDetectionStrategy, Component, Input, ViewEncapsulation} from '@angular/core';
import {WebPlayerUrls} from "../../web-player-urls.service";

@Component({
    selector: 'artists-links-list',
    templateUrl: './artists-links-list.component.html',
    styleUrls: ['./artists-links-list.component.scss'],
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class ArtistsLinksListComponent {

    /**
     * ArtistsLinksListComponent Constructor.
     */
    constructor(public urls: WebPlayerUrls) {}

    /**
     * List of artist names to render.
     */
    @Input() artists: string[];
}
