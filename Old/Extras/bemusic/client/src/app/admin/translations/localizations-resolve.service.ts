import { Injectable }             from '@angular/core';
import { Router, Resolve, ActivatedRouteSnapshot } from '@angular/router';
import {Localization} from "../../shared/types/models/Localization";
import {Localizations} from "../../shared/translations/localizations.service";

@Injectable()
export class LocalizationsResolve implements Resolve<Localization[]> {

    constructor(private localizations: Localizations, private router: Router) {}

    resolve(route: ActivatedRouteSnapshot): Promise<Localization[]> {
        return this.localizations.all().toPromise().then(response => {
            return response;
        }, () => {
            this.router.navigate(['/admin']);
            return false;
        }) as any;
    }
}