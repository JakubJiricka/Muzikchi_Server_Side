import {Injectable} from '@angular/core';
import {Router, Resolve, ActivatedRouteSnapshot} from '@angular/router';
import {UserLibrary} from "../user-library.service";
import {Track} from "../../../../shared/types/models/Track";
import {PaginationResponse} from "../../../../shared/types/pagination-response";
import {WebPlayerState} from "../../../web-player-state.service";

@Injectable()
export class LibraryTracksResolver implements Resolve<PaginationResponse<Track>> {

    constructor(
        private library: UserLibrary,
        private router: Router,
        private state: WebPlayerState
    ) {}

    resolve(route: ActivatedRouteSnapshot): Promise<PaginationResponse<Track>> {
        this.state.loading = true;

        if (this.library.tracks.alreadyFetched) {
            this.state.loading = false;
            return new Promise(resolve => resolve());
        }

        return this.library.tracks.fetch().toPromise().then(response => {
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