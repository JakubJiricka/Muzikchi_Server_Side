import {Injectable} from '@angular/core';
import {Router, Resolve, ActivatedRouteSnapshot} from '@angular/router';
import {WebPlayerState} from "../../../web-player-state.service";
import {Playlist} from "../../../../shared/types/models/Playlist";
import {Playlists} from "../../../playlists/playlists.service";
import {CurrentUser} from "../../../../auth/current-user";

@Injectable()
export class LibraryPlaylistsResolver implements Resolve<Playlist[]> {

    constructor(
        private playlists: Playlists,
        private router: Router,
        private state: WebPlayerState,
        private user: CurrentUser
    ) {}

    resolve(route: ActivatedRouteSnapshot): Promise<Playlist[]> {
        this.state.loading = true;

        return this.playlists.allUserPlaylists(this.user.get('id')).toPromise().then(response => {
            this.state.loading = false;

            if (response) {
                return response;
            } else {
                this.router.navigate(['/library']);
                return null;
            }
        }).catch(() => {
            this.state.loading = false;
            this.router.navigate(['/library']);
        }) as any;
    }
}