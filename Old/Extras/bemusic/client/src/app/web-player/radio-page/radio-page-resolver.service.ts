import {Injectable} from '@angular/core';
import {Router, Resolve, RouterStateSnapshot, ActivatedRouteSnapshot} from '@angular/router';
import {WebPlayerState} from "../web-player-state.service";
import {Artists} from "../artists/artists.service";
import {Track} from "../../shared/types/models/Track";
import {Artist} from "../../shared/types/models/Artist";
import {Tracks} from "../tracks/tracks.service";

@Injectable()
export class RadioPageResolver implements Resolve<{recommendations: Track[], seed: Artist|Track}> {

    constructor(
        private artists: Artists,
        private tracks: Tracks,
        private router: Router,
        private state: WebPlayerState
    ) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Promise<{recommendations: Track[], seed: Artist|Track}> {
        this.state.loading = true;
        let id = +route.paramMap.get('id'), request;

        if (route.data.type === 'artist') {
            request = this.artists.getRadioRecommendations(id);
        } else {
            request = this.tracks.getRadioRecommendations(id);
        }

        return request.toPromise().then(response => {
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