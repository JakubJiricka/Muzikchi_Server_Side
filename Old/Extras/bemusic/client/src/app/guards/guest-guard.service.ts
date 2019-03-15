import {Injectable} from '@angular/core';
import {CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, Router} from '@angular/router';
import {CurrentUser} from "../auth/current-user";
import {AuthService} from "../auth/auth.service";

@Injectable()
export class GuestGuard implements CanActivate {

    constructor(private currentUser: CurrentUser, private auth: AuthService, private router: Router) {}

    canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot) {
        if ( ! this.currentUser.isLoggedIn()) { return true; }

        this.router.navigate([this.auth.getRedirectUri()]);

        return false;
    }
}