import {Component, Injector, ViewEncapsulation} from '@angular/core';
import {ContextMenuComponent} from "../../context-menu/context-menu.component";
import {Artist} from "../../../shared/types/models/Artist";
import {Track} from "../../../shared/types/models/Track";

@Component({
    selector: 'artist-context-menu',
    templateUrl: './artist-context-menu.component.html',
    styleUrls: ['./artist-context-menu.component.scss'],
    encapsulation: ViewEncapsulation.None,
    host: {'class': 'context-menu'},
})
export class ArtistContextMenuComponent extends ContextMenuComponent<Artist> {

    /**
     * ArtistContextMenuComponent Constructor.
     */
    constructor(protected injector: Injector) {
        super(injector);
    }

    /**
     * Copy fully qualified album url to clipboard.
     */
    public copyLinkToClipboard() {
        super.copyLinkToClipboard('artist');
    }

    /**
     * Get all current artist tracks.
     */
    public getTracks(): Track[] {
        return [];
    }

    /**
     * Go to artist radio route.
     */
    public goToArtistRadio() {
        this.contextMenu.close();
        this.router.navigate(this.urls.artistRadio(this.item));
    }
}
