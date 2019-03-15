import {Injectable} from '@angular/core';
import {Router, Resolve, RouterStateSnapshot, ActivatedRouteSnapshot} from '@angular/router';
import {WebPlayerState} from "../../web-player-state.service";
import {Users} from "../users.service";
import {User} from "../../../shared/types/models/User";

@Injectable()
export class UserProfilePageResolver implements Resolve<User> {

    constructor(
        private users: Users,
        private router: Router,
        private state: WebPlayerState
    ) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Promise<User> {
        this.state.loading = true;

        const id = +route.paramMap.get('id');

        return this.users.get(id).toPromise().then(user => {
            this.state.loading = false;

            if (user) {
                return user;
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