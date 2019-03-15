import {Paginator} from "./paginator.service";
import {BehaviorSubject} from "rxjs/BehaviorSubject";
import {Observable} from "rxjs/Observable";
import {Observer} from "rxjs/Observer";

describe('PaginatorService', () => {

    let paginator: Paginator;

    beforeEach(() => {
        paginator = new Paginator({get: function() { return {get: function() {}} }} as any);
        paginator.serverUri = 'foo-bar';
    });

    it('checks whether there are any results', () => {
        //returns false if paginator has not been initiated yet
        expect(paginator.doesNotHaveResults()).toBeFalsy();

        //returns true if there are not results
        paginator.data = [];
        expect(paginator.doesNotHaveResults()).toBeTruthy();

        //returns false if there are results
        paginator.data = ['foo'];
        expect(paginator.doesNotHaveResults()).toBeFalsy();
    });

    it('checks whether there are pages after current one', () => {
        //there are no pages after
        paginator.params.currentPage = 1;
        paginator.params.lastPage = 1;
        expect(paginator.hasNext()).toBeFalsy();

        //there are pages after
        paginator.params.currentPage = 1;
        paginator.params.lastPage = 2;
        expect(paginator.hasNext()).toBeTruthy();
    });

    it('checks whether there are pages before current one', () => {
        //there are no pages before
        paginator.params.currentPage = 1;
        expect(paginator.hasPrev()).toBeFalsy();

        //there are pages before
        paginator.params.currentPage = 2;
        expect(paginator.hasPrev()).toBeTruthy();
    });

    it('checks whether current page is first one', () => {
        //is first page
        paginator.params.currentPage = 1;
        expect(paginator.isFirstPage()).toBeTruthy();

        //is not first page
        paginator.params.currentPage = 2;
        expect(paginator.isFirstPage()).toBeFalsy();
    });

    it('checks whether current page is last one', () => {
        //is last page
        paginator.params.lastPage = 1;
        paginator.params.currentPage = 1;
        expect(paginator.isLastPage()).toBeTruthy();

        //is not last page
        paginator.params.currentPage = 1;
        paginator.params.lastPage = 2;
        expect(paginator.isLastPage()).toBeFalsy();
    });

    it('goes to next page', () => {
        spyOn(paginator['httpClient'], 'get').and.returnValue(new BehaviorSubject({}));
        paginator.params.currentPage = 1;
        paginator.params.lastPage = 2;
        paginator.params.total = 20;
        paginator.params.perPage = 10;

        paginator.nextPage();

        expect(paginator['httpClient'].get).toHaveBeenCalledWith(jasmine.any(String), jasmine.objectContaining({page: 2}));
    });

    it('does not go to next page if there are no more pages after current one', () => {
        spyOn(paginator['httpClient'], 'get').and.returnValue(new BehaviorSubject({}));
        paginator.params.currentPage = 1;
        paginator.params.lastPage = 1;

        paginator.nextPage();

        expect(paginator['httpClient'].get).not.toHaveBeenCalled();
    });

    it('goes to previous page', () => {
        spyOn(paginator['httpClient'], 'get').and.returnValue(new BehaviorSubject({}));
        paginator.params.currentPage = 2;

        paginator.prevPage();

        expect(paginator['httpClient'].get).toHaveBeenCalledWith(jasmine.any(String), jasmine.objectContaining({page: 1}));
    });

    it('does not go to previous page if there are no more pages before current one', () => {
        spyOn(paginator['httpClient'], 'get').and.returnValue(new BehaviorSubject({}));
        paginator.params.currentPage = 1;

        paginator.prevPage();

        expect(paginator['httpClient'].get).not.toHaveBeenCalled();
    });

    it('goes to first page', () => {
        spyOn(paginator['httpClient'], 'get').and.returnValue(new BehaviorSubject({}));
        paginator.params.currentPage = 3;

        paginator.firstPage();

        expect(paginator['httpClient'].get).toHaveBeenCalledWith(jasmine.any(String), jasmine.objectContaining({page: 1}));
    });

    it('does not go to first page if paginator is already on first page', () => {
        spyOn(paginator['httpClient'], 'get').and.returnValue(new BehaviorSubject({}));
        paginator.params.currentPage = 1;

        paginator.firstPage();

        expect(paginator['httpClient'].get).not.toHaveBeenCalled();
    });

    it('goes to last page', () => {
        spyOn(paginator['httpClient'], 'get').and.returnValue(new BehaviorSubject({}));
        paginator.params.currentPage = 1;
        paginator.params.lastPage = 3;
        paginator.params.total = 30;
        paginator.params.perPage = 10;

        paginator.lastPage();

        expect(paginator['httpClient'].get).toHaveBeenCalledWith(jasmine.any(String), jasmine.objectContaining({page: 3}));
    });

    it('does not go to last page if paginator is already on last page', () => {
        spyOn(paginator['httpClient'], 'get').and.returnValue(new BehaviorSubject({}));
        paginator.params.currentPage = 3;
        paginator.params.lastPage = 3;

        paginator.lastPage();

        expect(paginator['httpClient'].get).not.toHaveBeenCalled();
    });

    it('refreshes paginator on param change', () => {
        spyOn(paginator['httpClient'], 'get').and.returnValue(new BehaviorSubject({}));
        paginator.params.perPage = 10;

        paginator.onParamChange('perPage');

        expect(paginator['httpClient'].get).toHaveBeenCalledWith(jasmine.any(String), jasmine.objectContaining({per_page: 10}));
    });

    it('sets paginator params', () => {
        paginator.params = {} as any;

        paginator.setParams({current_page: 1, total: 2, per_page: 3, last_page: 4, to: 5, from: 6});

        expect(paginator.params.currentPage).toEqual(1);
        expect(paginator.params.total).toEqual(2);
        expect(paginator.params.perPage).toEqual(3);
        expect(paginator.params.lastPage).toEqual(4);
        expect(paginator.params.to).toEqual(5);
        expect(paginator.params.from).toEqual(6);
    });

    it('refreshes paginator', () => {
        spyOn(paginator['httpClient'], 'get').and.returnValue(new BehaviorSubject({}));
        paginator.staticQueryParams = {foo: 'bar'};
        paginator.params.currentPage = 1;

        let request = paginator.refresh({page: 2, baz: 'qux'});

        expect(paginator['httpClient'].get).toHaveBeenCalledWith(jasmine.any(String), jasmine.objectContaining({foo: 'bar', page: 2, baz: 'qux'}));
        expect(request).toEqual(jasmine.any(Observable));
    });

    it('returns current server request when refreshing paginator if paginator is already loading', () => {
        spyOn(paginator['httpClient'], 'get').and.returnValue(new BehaviorSubject({}));
        paginator.isLoading = true;
        paginator['serverRequest'] = new BehaviorSubject({});

        let request = paginator['makeRequest']();

        expect(paginator['httpClient'].get).not.toHaveBeenCalled();
        expect(request).toEqual(paginator['serverRequest']);
    });

    it('makes pagination request to server', () => {
        let eventFired: any = false;
        let observer: Observer<any>;
        let observable = Observable.create(o => observer = o);
        spyOn(paginator['httpClient'], 'get').and.returnValue(observable);
        paginator.onNavigate.subscribe(data => eventFired = data);

        let request = paginator['makeRequest']({foo: 'bar'});

        //marks paginator as loading
        expect(paginator.isLoading).toBeTruthy();

        //sets server quest observable on paginator
        expect(paginator['serverRequest']).toEqual(jasmine.any(Observable));

        observer.next({total: 99, data: 'foo'});
        observer.complete();

        //makes call to server
        expect(paginator['httpClient'].get).toHaveBeenCalledWith(paginator.serverUri, jasmine.objectContaining({foo: 'bar'}));

        //marks paginator as not loading
        expect(paginator.isLoading).toBeFalsy();

        //destroys server request observable
        expect(paginator['serverRequest']).toBeNull();

        //sets pagination params on paginator
        expect(paginator.params.total).toEqual(99);

        //sets data on paginator
        expect(paginator.data).toEqual('foo');

        //emits "onNavigate" event
        expect(eventFired).toEqual({total: 99, data: 'foo'});

        //returns server request observable
        expect(request).toEqual(jasmine.any(Observable));
    });

    it('destroys paginator instance', () => {
        paginator.data = ['foo'];
        paginator.destroy();
        expect(paginator.onNavigate).toBeTruthy();
        expect(paginator.data).toBeFalsy();
    });
});