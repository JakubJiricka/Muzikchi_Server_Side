import {Injectable} from '@angular/core';
import {HttpCacheClient} from "../../shared/http-cache-client";

@Injectable()
export class GroupService {

    constructor(private httpClient: HttpCacheClient) {}

    /**
     * Fetch all existing user groups.
     */
    public getGroups() {
        return this.httpClient.getWithCache('groups?per_page=15');
    }

    /**
     * Create a new group.
     */
    public createNew(data) {
        return this.httpClient.post('groups', data);
    }

    /**
     * Update existing group.
     */
    public update(groupId, data) {
        return this.httpClient.put('groups/'+groupId, data);
    }

    /**
     * Delete group with given id.
     */
    public delete(groupId: number) {
        return this.httpClient.delete('groups/'+groupId);
    }

    /**
     * Add users to given group.
     */
    public addUsers(groupId: number, emails: string[]) {
        return this.httpClient.post('groups/'+groupId+'/add-users', {emails});
    }

    /**
     * Remove users from given group.
     */
    public removeUsers(groupId: number, userIds: number[]) {
        return this.httpClient.post('groups/'+groupId+'/remove-users', {ids: userIds});
    }
}