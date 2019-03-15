import { Injectable }             from '@angular/core';
import {Resolve, ActivatedRouteSnapshot } from '@angular/router';
import {AppHttpClient} from "../../shared/app-http-client.service";

@Injectable()
export class AnalyticsResolve implements Resolve<object> {

    constructor(private http: AppHttpClient) {}

    resolve(route: ActivatedRouteSnapshot): Promise<object> {
        return this.http.get('admin/analytics/stats').toPromise().then(response => {
            return response;
        }, () => {
            return false;
        }).catch(() => {
            //
        }) as any;
    }
}