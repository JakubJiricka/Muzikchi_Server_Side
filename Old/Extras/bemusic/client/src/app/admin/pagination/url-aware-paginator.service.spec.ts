import {UrlAwarePaginator} from "./url-aware-paginator.service";
import {BehaviorSubject} from "rxjs/BehaviorSubject";
import {Observable} from "rxjs/Observable";
import {fakeAsync, tick} from "@angular/core/testing";
import {Observer} from "rxjs/Observer";

xdescribe('UrlAwarePaginatorService', () => {

    let paginator: UrlAwarePaginator;

    beforeEach(() => {
        let router = {
            url: 'http://foo.bar?query=1',
            routerState: {
                snapshot: {root: {queryParams: {foo: 'bar'}}},
                root: {queryParams: Observable.of({})}
            },
            navigate: function() {},
        } as any;
        paginator = new UrlAwarePaginator({get: function() { return router; }} as any);
        paginator.httpClient = {get: function() {}} as any;
        paginator.serverUri = 'foo-bar';
    });

    it('paginates specified uri', fakeAsync(() => {
        let observer: Observer<any>;
        let observable = Observable.create(o => observer = o);

        let response: any = false;
        spyOn(paginator['httpClient'], 'get').and.returnValue(observable);

        let request = paginator.paginate('foo-uri', {staticParam: 'baz'});
        request.subscribe(data => response = data);

        observer.next('response');
        observer.complete();

        //sets server uri
        expect(paginator.serverUri).toEqual('foo-uri');

        //calls server with all params merged and transformed
        expect(paginator['httpClient'].get).toHaveBeenCalledWith(
            'foo-uri',
            jasmine.objectContaining({page: 1, routeParam: 'foo', queryParam: 'bar', staticParam: 'baz'})
        );

        //returns observable
        expect(request).toEqual(jasmine.any(Observable));

        //returns data from server
        tick();
        expect(response).toEqual('response');
    }));

    it('refreshes paginator', () => {
        let observer: Observer<any>;
        let observable = Observable.create(o => observer = o);
        spyOn(paginator['httpClient'], 'get').and.returnValue(observable);
        paginator.params.currentPage = 1;
        let paginateEmitted: any = false;

        paginator.paginate('foo').subscribe(response => paginateEmitted = response);
        observer.next('response');
        observer.complete();

        let request = paginator.refresh({page: 2, baz: 'qux'});

        //makes 2 calls to backend, one for initial paginate and one for refresh
        expect(paginator['httpClient'].get).toHaveBeenCalledTimes(2);

        //makes refresh call to server with correct params
        expect(paginator['httpClient'].get).toHaveBeenCalledWith(
            jasmine.any(String),
            jasmine.objectContaining({routeParam: 'foo', queryParam: 'bar', page: 2, baz: 'qux'})
        );

        //returns observable from refresh call
        expect(request).toEqual(jasmine.any(Observable));

        //emits new value via observable returned from "paginate" method
        expect(paginateEmitted).toEqual('response');
    });

    it('navigates to specified page', () => {
        spyOn(paginator['router'], 'navigate').and.returnValue(new BehaviorSubject({}));
        paginator.params.currentPage = 1;
        paginator.params.lastPage = 3;
        paginator.params.total = 30;
        paginator.params.perPage = 10;

        paginator.nextPage();

        //navigates to url with new page query param and preserves other query params
        expect(paginator['router']['navigate']).toHaveBeenCalledWith([], {queryParams: {foo: 'bar', page: 2}});
    });

    it('does not navigate to non-existing page', () => {
        spyOn(paginator['router'], 'navigate').and.returnValue(new BehaviorSubject({}));
        paginator.params.currentPage = 2;
        paginator.params.lastPage = 2;
        paginator.params.total = 20;
        paginator.params.perPage = 10;

        paginator['goToPage'](3);

        //navigates to last existing page
        expect(paginator['router']['navigate']).toHaveBeenCalledWith([], {queryParams: {foo: 'bar', page: 2}});
    });

    it('updates paginator after param change by user', () => {
        spyOn(paginator['router'], 'navigate').and.returnValue(new BehaviorSubject({}));
        paginator.params.perPage = 25;

        paginator.onParamChange('perPage');

        //navigates to url with new page query param and preserves other query params
        expect(paginator['router']['navigate']).toHaveBeenCalledWith([], {queryParams: {foo: 'bar', per_page: 25}});
    });

    it('destroys paginator', () => {
        let observer: Observer<any>, observable = Observable.create(o => observer = o);
        paginator['subscription'] = observable.subscribe();
        paginator.data = ['foo'];
        expect(paginator.onNavigate).toBeTruthy();
        expect(paginator['onRefresh']).toBeTruthy();
        expect(paginator['subscription'].closed).toEqual(false);

        paginator.destroy();

        expect(paginator.onNavigate).toBeTruthy();
        expect(paginator.data).toBeFalsy();
        expect(paginator['subscription'].closed).toEqual(true);
    });
});