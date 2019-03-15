import {Injectable} from '@angular/core';
import {Resolve, RouterStateSnapshot, ActivatedRouteSnapshot} from '@angular/router';
import {Genres} from "../genres.service";
import {Genre} from "../../../shared/types/models/Genre";
import {WebPlayerState} from "../../web-player-state.service";

@Injectable()
export class PopularGenresResolver implements Resolve<Genre[]> {

    constructor(
        private genres: Genres,
        private state: WebPlayerState
    ) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Promise<Genre[]> {
        this.state.loading = true;

        return this.genres.getPopular().toPromise().then(genres => {
            this.state.loading = false;

            if (genres) {
                return genres;
            } else {
                return null;
            }
        }).catch(() => {
            this.state.loading = false;
        }) as any;
    }
}