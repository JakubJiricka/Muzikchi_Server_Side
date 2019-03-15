import {Component, Injector, ViewEncapsulation} from '@angular/core';
import {Album} from "../../../shared/types/models/Album";
import {WpUtils} from "../../web-player-utils";
import {ContextMenuComponent} from "../../context-menu/context-menu.component";

@Component({
    selector: 'album-context-menu',
    templateUrl: './album-context-menu.component.html',
    styleUrls: ['./album-context-menu.component.scss'],
    encapsulation: ViewEncapsulation.None,
    host: {'class': 'context-menu'},
})
export class AlbumContextMenuComponent extends ContextMenuComponent<Album> {

    /**
     * AlbumContextMenuComponent Constructor.
     */
    constructor(protected injector: Injector) {
        super(injector);
    }

    /**
     * Copy fully qualified album url to clipboard.
     */
    public copyLinkToClipboard() {
        super.copyLinkToClipboard('album');
    }

    /**
     * Get tracks that should be used by context menu.
     */
    public getTracks() {
        return WpUtils.assignAlbumToTracks(this.item.tracks, this.item)
    }
}
