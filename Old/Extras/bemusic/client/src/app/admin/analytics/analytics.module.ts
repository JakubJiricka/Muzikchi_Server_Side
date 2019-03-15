import {NgModule} from '@angular/core';
import {AnalyticsRoutingModule} from "./analytics-routing.module";
import {AnalyticsComponent} from "./analytics.component";
import {AnalyticsResolve} from "./analytics-resolve.service";
import {SharedModule} from "../../shared/shared.module";

@NgModule({
    imports: [
        SharedModule,
        AnalyticsRoutingModule
    ],
    declarations: [
        AnalyticsComponent
    ],
    providers: [
        AnalyticsResolve
    ]
})
export class AnalyticsModule {
}
