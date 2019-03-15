import {Injectable} from '@angular/core';
import {Resolve, RouterStateSnapshot, ActivatedRouteSnapshot, Router} from '@angular/router';
import {ArtistService} from "../artist.service";
import {Artists} from "../artists.service";
import {Player} from "../../player/player.service";
import {WebPlayerState} from "../../web-player-state.service";
import {WebPlayerUrls} from "../../web-player-urls.service";

@Injectable()
export class ArtistResolver implements Resolve<ArtistService> {

    constructor(
        private artists: Artists,
        private player: Player,
        private state: WebPlayerState,
        private router: Router,
        private urls: WebPlayerUrls
    ) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Promise<ArtistService> {
        this.state.loading = true;

        let params = {'with': ['similar', 'genres'], top_tracks: true}, id;

        if (route.paramMap.has('id')) {
            id = route.paramMap.get('id');
        } else {
            id = encodeURIComponent(this.urls.encodeItemName(route.paramMap.get('name')));
            params['by_name'] = true;
        }

        const artist = new ArtistService(this.artists, this.player);

        return artist.init(id, params).then(() => {
            this.state.loading = false;
            return artist;
        }).catch(() => {
            this.state.loading = false;
            this.router.navigate(['/'])
        }) as any;
    }
}