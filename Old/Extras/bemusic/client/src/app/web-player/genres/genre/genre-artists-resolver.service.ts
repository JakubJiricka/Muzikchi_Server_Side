import {Injectable} from '@angular/core';
import {Router, Resolve, RouterStateSnapshot, ActivatedRouteSnapshot} from '@angular/router';
import {Genres} from "../genres.service";
import {Artist} from "../../../shared/types/models/Artist";
import {Genre} from "../../../shared/types/models/Genre";
import {PaginationResponse} from "../../../shared/types/pagination-response";
import {WebPlayerState} from "../../web-player-state.service";

@Injectable()
export class GenreArtistsResolver implements Resolve<{artistsPagination: PaginationResponse<Artist>, model: Genre}> {

    constructor(
        private genres: Genres,
        private router: Router,
        private state: WebPlayerState
    ) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Promise<{artistsPagination: PaginationResponse<Artist>, model: Genre}> {
        this.state.loading = true;

        const name = route.paramMap.get('name');

        return this.genres.getGenreArtists(name).toPromise().then(artists => {
            this.state.loading = false;

            if (artists) {
                return artists;
            } else {
                this.router.navigate(['/popular-genres']);
                return null;
            }
        }).catch(() => {
            this.state.loading = false;
            this.router.navigate(['/popular-genres']);
        }) as any;
    }
}