import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from "@angular/common/http";
import {Observable} from "rxjs/Observable";
import {HttpErrorHandler} from "./http-error-handler.service";

@Injectable()
export class AppHttpClient {

    static prefix = 'secure';

    /**
     * AppHttpClient Constructor.
     */
    constructor(protected httpClient: HttpClient, protected errorHandler: HttpErrorHandler) {}

    public get<T>(uri: string, params = {}): Observable<T> {
        const httpParams = this.generateHttpParams(params);
        return this.httpClient.get<T>(this.prefixUri(uri), {params: httpParams}).catch(err => this.errorHandler.handle(err));
    }

    public post<T>(uri: string, params: object = null): Observable<T> {
        return this.httpClient.post<T>(this.prefixUri(uri), params).catch(err => this.errorHandler.handle(err));
    }

    public put<T>(uri: string, params: object = {}): Observable<T> {
        params['_method'] = 'PUT';
        return this.httpClient.post<T>(this.prefixUri(uri), params).catch(err => this.errorHandler.handle(err));
    }

    public delete<T>(uri: string, params: object = {}): Observable<T> {
        params['_method'] = 'DELETE';
        return this.httpClient.post<T>(this.prefixUri(uri), params).catch(err => this.errorHandler.handle(err));
    }

    /**
     * Prefix specified uri with backend API prefix.
     */
    private prefixUri(uri: string) {
        return AppHttpClient.prefix+'/'+uri
    }

    /**
     * Generate http params for GET request.
     */
    private generateHttpParams(params: object) {
        let httpParams = new HttpParams();

        for(let key in params) {
            httpParams = httpParams.append(key, params[key]);
        }

        return httpParams;
    }
}
