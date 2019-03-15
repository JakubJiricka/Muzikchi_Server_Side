import {EventEmitter, Injector} from '@angular/core';
import {Observable} from "rxjs";
import {AppHttpClient} from "../../shared/app-http-client.service";
import {utils} from "../../shared/utils";

export class Paginator {

    /**
     * Fired when paginator moves to different page or is refreshed.
     */
    public onNavigate = new EventEmitter();

    /**
     * Current paginator parameters.
     */
    public params = Paginator.getInitialParams();

    /**
     * Static query params that should be included with server API calls.
     */
    public staticQueryParams = {};

    /**
     * Paginated data returned from server.
     *
     * Important to not initiate it with empty array
     * so we can know if this paginator has already
     * made at least one call to the server. It's used
     * across the app to show 'no results found' message.
     */
    public data: any[];

    /**
     * True if server API call is currently in progress.
     */
    public isLoading = false;

    /**
     * Observable for server API request,
     * if one is currently in progress.
     */
    protected serverRequest: Observable<any>;

    /**
     * HttpClient service instance.
     */
    public httpClient: AppHttpClient;

    /**
     * Paginator Constructor.
     */
    constructor(protected injector: Injector, public serverUri?: string) {
        this.httpClient = this.injector.get(AppHttpClient);
    }

    /**
     * Check if paginator does NOT have any results.
     * Will return false if paginator was not initiated yet.
     */
    public doesNotHaveResults(): boolean {
        return this.data && ! this.data.length;
    }

    /**
     * Check if paginator has any results.
     */
    public hasResults() {
        return this.data && this.data.length;
    }

    /**
     * Check if there are any more pages after current one.
     */
    public hasNext() {
        return this.params.currentPage !== this.params.lastPage;
    }

    /**
     * Check if there are any more pages before current one.
     */
    public hasPrev() {
        return this.params.currentPage > 1;
    }

    /**
     * Check if current page is the first one.
     */
    public isFirstPage() {
        return this.params.currentPage === 1;
    }

    /**
     * Check if current page is the last one.
     */
    public isLastPage() {
        return this.params.lastPage === this.params.currentPage;
    }

    /**
     * Go to next page.
     */
    public nextPage() {
        if (this.hasNext()) {
            this.goToPage(this.params.currentPage+1);
        }
    }

    /**
     * Go to previous page.
     */
    public prevPage() {
        if (this.hasPrev()) {
            this.goToPage(this.params.currentPage-1);
        }
    }

    /**
     * Go to first page.
     */
    public firstPage() {
        if ( ! this.isFirstPage()) {
            this.goToPage(1);
        }
    }

    /**
     * Go to last page.
     */
    public lastPage() {
        if ( ! this.isLastPage()) {
            this.goToPage(this.params.lastPage);
        }
    }

    /**
     * Go to specified page.
     */
    protected goToPage(page: number) {
        this.refresh(this.normalizeParams({page}));
    }

    /**
     * Normalize specified router params.
     */
    protected normalizeParams(params: Object) {
        let lastPage = Math.ceil(this.params.total / this.params.perPage);

        //navigate to last page is specified page is invalid
        if (params['page'] && params['page'] > lastPage) {
            params['page'] = lastPage;
        }

        return params;
    }

    /**
     * Fired when any of router parameters are changed by user (via pagination controls).
     */
    public onParamChange(name: string) {
        let params = {};
        params[utils.toSnakeCase(name)] = this.params[name];
        this.refresh(params);
    }

    /**
     * Set pagination parameters.
     */
    public setParams(params) {
        if ( ! params) return;

        this.params.currentPage = params.current_page;
        this.params.total       = params.total;
        this.params.perPage     = params.per_page;
        this.params.lastPage    = params.last_page;
        this.params.to          = params.to;
        this.params.from        = params.from;
    }

    /**
     * Refresh paginator with specified params.
     */
    public refresh(params = {}): Observable<any> {
        return this.makeRequest(params);
    }

    /**
     * Make paginated request to specified page.
     */
    protected makeRequest(params = {}): Observable<any> {
        if (this.isLoading) return this.serverRequest;

        this.isLoading = true;

        this.serverRequest = this.httpClient.get(this.serverUri, this.getDefaultParams(params)).share();

        this.serverRequest.finally(() => {
            this.isLoading = false;
            this.serverRequest = null;
        }).subscribe(response => {
            this.setParams(response);
            this.data = response.data;
            this.onNavigate.emit(response);
        }, () => {});

        return this.serverRequest;
    }

    /**
     * Get default params for paginator requests.
     */
    protected getDefaultParams(params = {}): Object {
        return Object.assign(
            {page: this.params.currentPage, per_page: this.params.perPage},
            this.staticQueryParams,
            params
        );
    }

    /**
     * Get initial paginator params.
     */
    protected static getInitialParams() {
        return {
            currentPage: 1,
            total: 1,
            perPage: 15,
            lastPage: 1,
            to: 1,
            from: 1,
        };
    }

    /**
     * Remove all observables and unsubscribe from route params.
     */
    public destroy() {
        this.onNavigate = new EventEmitter();
        this.params = Paginator.getInitialParams();
        this.staticQueryParams = {};
        this.isLoading = false;
        this.serverRequest = null;
        this.data = null;
    }
}