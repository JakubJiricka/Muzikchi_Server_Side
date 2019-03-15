import {Injectable} from '@angular/core';
import {Router, Resolve, RouterStateSnapshot, ActivatedRouteSnapshot} from '@angular/router';
import {WebPlayerState} from "../../web-player-state.service";
import {Tracks} from "../tracks.service";
import {Track} from "../../../shared/types/models/Track";

@Injectable()
export class TopTracksPageResolver implements Resolve<Track[]> {

    constructor(
        private tracks: Tracks,
        private router: Router,
        private state: WebPlayerState
    ) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Promise<Track[]> {
        this.state.loading = true;

        return this.tracks.getTop().toPromise().then(tracks => {
            this.state.loading = false;

            if (tracks) {
                return tracks;
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