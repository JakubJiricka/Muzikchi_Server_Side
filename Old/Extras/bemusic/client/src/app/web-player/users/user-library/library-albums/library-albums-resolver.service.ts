import {Injectable} from '@angular/core';
import {Router, Resolve, ActivatedRouteSnapshot} from '@angular/router';
import {UserLibrary} from "../user-library.service";
import {Album} from "../../../../shared/types/models/Album";
import {WebPlayerState} from "../../../web-player-state.service";

@Injectable()
export class LibraryAlbumsResolver implements Resolve<Album[]> {

    constructor(
        private library: UserLibrary,
        private router: Router,
        private state: WebPlayerState
    ) {}

    resolve(route: ActivatedRouteSnapshot): Promise<Album[]> {
        this.state.loading = true;

        return this.library.fetchAlbums().toPromise().then(response => {
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