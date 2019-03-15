import {Injectable} from '@angular/core';
import {Router, Resolve, ActivatedRouteSnapshot} from '@angular/router';
import {User} from "../../../shared/types/models/User";
import {Users} from "../users.service";
import {CurrentUser} from "../../../auth/current-user";
import {Observable} from "rxjs/Observable";
import {ValueLists} from "../../../shared/value-lists.service";

@Injectable()
export class AccountSettingsResolve implements Resolve<{user: User, selects: Object}> {

    constructor(
        private users: Users,
        private router: Router,
        private currentUser: CurrentUser,
        private values: ValueLists
    ) {}

    resolve(route: ActivatedRouteSnapshot): Promise<{user: User, selects: Object}> {
        return Observable.forkJoin(
            this.users.get(this.currentUser.get('id')),
            this.values.getValuesForSelects(),
        ).toPromise().then(response => {
            return {user: response[0], selects: response[1]};
        }, () => {
            this.router.navigate(['/']);
            return false;
        }) as any;
    }
}