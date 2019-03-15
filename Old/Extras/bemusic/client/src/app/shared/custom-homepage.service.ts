import {Injectable} from '@angular/core';
import {Route, Router, Routes} from "@angular/router";
import {Settings} from "./settings.service";
import {PageComponent} from "../pages/page.component";

@Injectable()
export class CustomHomepage {

    private defaultComponents: Routes = [
        {path: 'login', redirectTo: '/login', pathMatch: 'full'},
        {path: 'register', redirectTo: '/register', pathMatch: 'full'},
    ];

    constructor(private router: Router, private settings: Settings) {
    }

    public select(userRoutes: Routes = []) {
        this.defaultComponents = this.defaultComponents.concat(userRoutes);

        const type = this.settings.get('homepage.type', 'default'),
              value = this.settings.get('homepage.value');

        switch (type) {
            case 'default':
                return;
            case 'component':
                return this.setComponentAsHomepage(value);
            case 'page':
                return this.setCustomPageAsHomepage(value);
        }
    }

    private setComponentAsHomepage(name: string) {
        const route = this.defaultComponents.find(comp => comp.path === name);
        this.addRoute(route);
    }

    private setCustomPageAsHomepage(id: string|number) {
        const route = {component: PageComponent, data: {id}};
        this.addRoute(route);
    }

    private getParentHomeRoute(): Route {
        return this.router.config.find(route => {
            return route.data && route.data.name === 'parent-home-route';
        });
    }

    private addRoute(route: Route) {
        const parent = this.getParentHomeRoute();
        route = this.prepareRoute(route);

        //use child routes if parent exists, otherwise use base router config
        const routes = parent ? parent.children : this.router.config;

        //remove already existing home route
        const i = routes.findIndex(route => route.path === '');

        //add new route specified by user
        if (i > -1) {
            routes[i] = route;
        } else {
            routes.unshift(route);
        }
    }

    private prepareRoute(route: Route) {
        route.path = '';
        if ( ! route.data) route.data = {};
        route.data.name = 'home';
        return route;
    }
}
