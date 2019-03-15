import {Injectable} from '@angular/core';
import {Router, Resolve, RouterStateSnapshot, ActivatedRouteSnapshot} from '@angular/router';
import {Search} from "../search.service";
import {WebPlayerState} from "../../web-player-state.service";
import {SearchResponse} from "../../../shared/types/search-response";

@Injectable()
export class SearchResolver implements Resolve<SearchResponse> {

    private lastSearch = {query: '', results: {}};

    constructor(
        private search: Search,
        private router: Router,
        private state: WebPlayerState
    ) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Promise<SearchResponse> {
        this.state.loading = true;

        const query = route.paramMap.get('query');

        if (this.lastSearch.query === query) {
            this.state.loading = false;
            return new Promise(resolve => resolve(this.lastSearch.results)) as any;
        }

        return this.search.everything(query, {limit: 20}).toPromise().then(results => {
            this.state.loading = false;

            if (results) {
                this.lastSearch = {query, results};
                return results;
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