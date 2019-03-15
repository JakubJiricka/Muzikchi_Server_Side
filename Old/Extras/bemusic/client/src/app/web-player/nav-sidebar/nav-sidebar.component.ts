import {Component, ViewEncapsulation} from '@angular/core';
import {Settings} from "../../shared/settings.service";
import {SearchSlideoutPanel} from "../search/search-slideout-panel/search-slideout-panel.service";
import {CurrentUser} from "../../auth/current-user";
import {Player} from "../player/player.service";
import {WebPlayerUrls} from "../web-player-urls.service";
import {AuthService} from "../../auth/auth.service";
import {UserPlaylists} from "../playlists/user-playlists.service";
import {ModalService} from "../../shared/modal/modal.service";
import {CrupdatePlaylistModalComponent} from "../playlists/crupdate-playlist-modal/crupdate-playlist-modal.component";
import {Router} from "@angular/router";
import {Track} from "../../shared/types/models/Track";

@Component({
    selector: 'nav-sidebar',
    templateUrl: './nav-sidebar.component.html',
    styleUrls: ['./nav-sidebar.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class NavSidebarComponent {

    /**
     * NavSidebarComponent Constructor.
     */
    constructor(
        public settings: Settings,
        public searchPanel: SearchSlideoutPanel,
        public currentUser: CurrentUser,
        public player: Player,
        public urls: WebPlayerUrls,
        public auth: AuthService,
        public playlists: UserPlaylists,
        private modal: ModalService,
        private router: Router
    ) {}

    public openNewPlaylistModal() {
        if ( ! this.currentUser.isLoggedIn()) {
            this.router.navigate(['/login']);
        }

        this.modal.show(CrupdatePlaylistModalComponent).onDone.subscribe(playlist => {
            this.playlists.add(playlist);
            this.router.navigate(this.urls.playlist(playlist));
        });
    }

    /**
     * Get image for specified track.
     */
    public getTrackImage(track: Track) {
        if ( ! track || ! track.album) return this.settings.getDefaultImage('album');
        return track.album.image;
    }
}
