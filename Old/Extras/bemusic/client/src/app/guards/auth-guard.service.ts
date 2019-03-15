import {Injectable} from '@angular/core';
import {CanActivate, Router, ActivatedRouteSnapshot, RouterStateSnapshot, CanActivateChild} from '@angular/router';
import {CurrentUser} from "../auth/current-user";

@Injectable()
export class AuthGuard implements CanActivate, CanActivateChild {

    constructor(private currentUser: CurrentUser, private router: Router) {}

    canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot) {
        return this.handle(state);
    }

    canActivateChild(route: ActivatedRouteSnapshot, state: RouterStateSnapshot) {
        return this.handle(state)
    }

    private handle(state: RouterStateSnapshot) {
        if (this.currentUser.isLoggedIn()) { return true; }

        this.currentUser.redirectUri = state.url;

        this.router.navigate(['/login']);

        return false;
    }
}