import {Injectable} from '@angular/core';
import {CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, Router} from '@angular/router';
import {Observable} from 'rxjs/Observable';
import {WebPlayerUrls} from "../../web-player-urls.service";

@Injectable()
export class SearchTabValidGuard implements CanActivate {

    readonly validTabs = ['artists', 'albums', 'songs', 'users', 'playlists'];

    constructor(private router: Router, private urls: WebPlayerUrls) {}

    canActivate(next: ActivatedRouteSnapshot, state: RouterStateSnapshot): boolean {
        const valid = this.validTabs.indexOf(next.params.tab) > -1;
        if ( ! valid) this.router.navigate(this.urls.search(next.params.query));
        return valid;
    }
}
