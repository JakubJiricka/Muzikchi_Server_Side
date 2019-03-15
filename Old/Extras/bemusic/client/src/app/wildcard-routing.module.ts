import {NotFoundPageComponent} from "./shared/not-found/not-found-page.component";
import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";

const routes: Routes = [
    {
        path: '**',
        pathMatch: 'full',
        component: NotFoundPageComponent
    },
    {
        path: '404',
        pathMatch: 'full',
        component: NotFoundPageComponent
    },
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule],
    declarations: [
        NotFoundPageComponent
    ],
})
export class WildcardRoutingModule { }