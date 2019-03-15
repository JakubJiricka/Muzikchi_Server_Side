import {Component, Injector, ViewEncapsulation} from '@angular/core';
import {TrackContextMenuComponent} from "../../tracks/track-context-menu/track-context-menu.component";
import {Player} from "../../player/player.service";
import {UserLibrary} from "../../users/user-library/user-library.service";
import {PlaylistService} from "../playlist.service";
import {Playlists} from "../playlists.service";
import {Lyrics} from "../../lyrics/lyrics.service";

@Component({
    selector: 'playlist-track-context-menu',
    templateUrl: './playlist-track-context-menu.component.html',
    encapsulation: ViewEncapsulation.None,
    host: {'class': 'context-menu'},
})
export class PlaylistTrackContextMenuComponent extends TrackContextMenuComponent {

    /**
     * PlaylistTrackContextMenuComponent Constructor.
     */
    constructor(
        protected player: Player,
        protected userLibrary: UserLibrary,
        protected injector: Injector,
        protected playlist: PlaylistService,
        protected playlists: Playlists,
        protected lyrics: Lyrics,
    ) {
        super(player, userLibrary, injector, lyrics);
    }

    /**
     * Remove track from currently active playlist.
     */
    public removeFromPlaylist() {
        this.playlists.removeTracks(this.params.playlistId, this.getTracks()).subscribe();
        this.contextMenu.close();
    }
}
