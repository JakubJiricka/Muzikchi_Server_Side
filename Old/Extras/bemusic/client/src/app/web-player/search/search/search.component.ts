import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {ActivatedRoute} from "@angular/router";
import {WebPlayerUrls} from "../../web-player-urls.service";
import {FormControl} from "@angular/forms";
import {Observable} from "rxjs/Observable";
import {Search} from "../search.service";
import {WebPlayerState} from "../../web-player-state.service";
import {Settings} from "../../../shared/settings.service";
import {SearchResponse} from "../../../shared/types/search-response";

@Component({
    selector: 'search',
    templateUrl: './search.component.html',
    styleUrls: ['./search.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class SearchComponent implements OnInit {

    public searchTerm: string;

    /**
     * Whether search is currently in progress.
     */
    public searching = false;

    /**
     * Whether search was already performed at least once.
     */
    public searchedOnce = false;

    /**
     * Currently active search page tab.
     */
    private activeTab: string = 'topResults';

    /**
     * Search input control.
     */
    public searchControl = new FormControl();

    /**
     * Search results.
     */
    public results: SearchResponse;

    /**
     * SearchComponent Constructor.
     */
    constructor(
        private route: ActivatedRoute,
        public urls: WebPlayerUrls,
        private search: Search,
        public state: WebPlayerState,
        public settings: Settings
    ) {}

    ngOnInit() {
        this.route.data.subscribe(data => {
            this.results = data.results;
        });

        this.route.params.subscribe(params => {
            this.searchTerm = params.query;
            this.searchControl.setValue(this.searchTerm, {emitEvent: false});
            this.activeTab = params.tab || 'topResults';
            if (this.searchTerm) this.searchedOnce = true;
        });

        this.bindToSearchQuery();
    }

    public activeTabIs(name: string) {
        return this.activeTab === name;
    }

    /**
     * Get router link for specified search tab.
     */
    public getRouterLink(tab?: string) {
        return this.urls.search(this.searchControl.value, tab);
    }

    /**
     * Check if any results have been found.
     */
    public hasResults(): boolean {
        if ( ! this.results || ! Object.keys(this.results)) return false;

        for (let type in this.results) {
            if (this.results[type].length) return true;
        }
    }

    /**
     * Check if there's a search term.
     */
    public hasSearchTerm() {
        return this.searchTerm || this.searchControl.value;
    }

    /**
     * Clear current search and input.
     */
    public clearSearch() {
        this.searchControl.setValue(null);
        this.searchTerm = null;
    }

    /**
     * Perform a search when user types into search input.
     */
    private bindToSearchQuery() {
        this.searchControl.valueChanges
            .debounceTime(400)
            .distinctUntilChanged()
            .switchMap(query => {
                this.searching = true;
                if ( ! query) return Observable.of(this.getEmptyResults());
                return this.search.everything(query, {limit: 20});
            }).catch(() => {
                return Observable.of(this.getEmptyResults());
            }).subscribe(response => {
                this.results = response || this.getEmptyResults();
                this.searching = false;
                this.searchedOnce = true;
            });
    }

    /**
     * Get empty search results set.
     */
    private getEmptyResults(): SearchResponse {
        return {
            artists: [],
            albums: [],
            tracks: [],
            playlists: [],
            users: [],
        }
    }
}
