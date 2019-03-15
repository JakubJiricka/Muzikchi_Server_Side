import {Injectable} from '@angular/core';
import {Router, Resolve, ActivatedRouteSnapshot} from '@angular/router';
import {UserLibrary} from "../user-library.service";
import {Artist} from "../../../../shared/types/models/Artist";
import {WebPlayerState} from "../../../web-player-state.service";

@Injectable()
export class LibraryArtistsResolver implements Resolve<Artist[]> {

    constructor(
        private library: UserLibrary,
        private router: Router,
        private state: WebPlayerState
    ) {}

    resolve(route: ActivatedRouteSnapshot): Promise<Artist[]> {
        this.state.loading = true;

        return this.library.fetchArtists().toPromise().then(response => {
            this.state.loading = false;

            if (response) {
                return response;
            } else {
                this.router.navigate(['/']);
                return null;
            }
        }).catch(() => {
            this.state.loading = false;
            this.router.navigate(['/']);
        }) as any;
    }
}