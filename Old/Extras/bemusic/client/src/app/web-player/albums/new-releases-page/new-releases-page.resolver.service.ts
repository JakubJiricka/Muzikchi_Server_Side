import {Injectable} from '@angular/core';
import {Router, Resolve, RouterStateSnapshot, ActivatedRouteSnapshot} from '@angular/router';
import {Album} from "../../../shared/types/models/Album";
import {WebPlayerState} from "../../web-player-state.service";
import {Albums} from "../albums.service";

@Injectable()
export class NewReleasesPageResolver implements Resolve<Album[]> {

    constructor(
        private albums: Albums,
        private router: Router,
        private state: WebPlayerState
    ) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Promise<Album[]> {
        this.state.loading = true;

        return this.albums.getNewReleases().toPromise().then(albums => {
            this.state.loading = false;

            if (albums) {
                return albums;
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