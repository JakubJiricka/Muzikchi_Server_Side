import {RouterModule, Routes} from '@angular/router';
import {NgModule} from "@angular/core";
import {AnalyticsComponent} from "./analytics.component";
import {AnalyticsResolve} from "./analytics-resolve.service";
import {AuthGuard} from "../../guards/auth-guard.service";

const routes: Routes = [
    {
        path: '',
        component: AnalyticsComponent,
        resolve: {stats: AnalyticsResolve},
        canActivate: [AuthGuard],
        data: {permissions: ['reports.view']}
    },
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class AnalyticsRoutingModule {}