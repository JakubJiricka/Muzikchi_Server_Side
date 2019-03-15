import {Injectable} from '@angular/core';
import {AppHttpClient} from "../../shared/app-http-client.service";
import {Observable} from "rxjs/Observable";
import {User} from "../../shared/types/models/User";

@Injectable()
export class Users {

    /**
     * Users REST service constructor.
     */
    constructor(private http: AppHttpClient) {}

    /**
     * Get user matching specified ID.
     */
    public get(id: number): Observable<User> {
        return this.http.get(`users/${id}`);
    }

    /**
     * Follow specified user with currently logged in user.
     */
    public follow(id: number) {
        return this.http.post(`users/${id}/follow`);
    }

    /**
     * Unfollow specified user with currently logged in user.
     */
    public unfollow(id: number) {
        return this.http.post(`users/${id}/unfollow`);
    }

    /**
     * Fetch users without pagination.
     */
    public getAll(params = null) {
        return this.http.get('users', params).map(response => response['data']);
    }

    /**
     * Create a new user.
     */
    public create(payload: Object) {
        return this.http.post('users', payload);
    }

    /**
     * Update existing user.
     */
    public update(id: number, payload: Object): Observable<User> {
        return this.http.put('users/'+id, payload);
    }

    /**
     * Change specified user password.
     */
    public changePassword(id: number, payload: Object): Observable<User> {
        return this.http.post('users/'+id+'/password/change', payload);
    }

    /**
     * Attach specified groups to user.
     */
    public attachGroups(id: number, payload = {}): Observable<any> {
        return this.http.post('users/'+id+'/groups/attach', payload);
    }

    /**
     * Detach specified groups from user.
     */
    public detachGroups(id: number, payload = {}): Observable<any> {
        return this.http.post('users/'+id+'/groups/detach', payload);
    }

    /**
     * Add specified permissions to user.
     */
    public addPermissions(id: number, payload = {}): Observable<{data: User}> {
        return this.http.post('users/'+id+'/permissions/add', payload);
    }

    /**
     * Remove specified permissions from user.
     */
    public removePermissions(id: number, payload = {}): Observable<{data: User}> {
        return this.http.post('users/'+id+'/permissions/remove', payload);
    }

    /**
     * Upload and attach avatar to specified user.
     */
    public uploadAvatar(id: number, files: FileList): Observable<User> {
        let payload = new FormData();
        payload.append('avatar', files.item(0));
        return this.http.post('users/'+id+'/avatar', payload);
    }

    /**
     * Delete specified user's avatar.
     */
    public deleteAvatar(id: number): Observable<User> {
        return this.http.delete('users/'+id+'/avatar');
    }

    /**
     * Delete multiple users.
     */
    public deleteMultiple(ids: number[]) {
        return this.http.delete('users/delete-multiple', {ids});
    }
}
