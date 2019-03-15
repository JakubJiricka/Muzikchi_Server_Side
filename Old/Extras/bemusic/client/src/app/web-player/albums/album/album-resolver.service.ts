import {Injectable} from '@angular/core';
import {Router, Resolve, RouterStateSnapshot, ActivatedRouteSnapshot} from '@angular/router';
import {Album} from "../../../shared/types/models/Album";
import {Albums} from "../albums.service";
import {WebPlayerState} from "../../web-player-state.service";

@Injectable()
export class AlbumResolver implements Resolve<Album> {

    constructor(
        private albums: Albums,
        private router: Router,
        private state: WebPlayerState
    ) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Promise<Album> {
        this.state.loading = true;

        const id = +route.paramMap.get('id');

        return this.albums.get(id).toPromise().then(album => {
            this.state.loading = false;

            if (album) {
                return album;
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