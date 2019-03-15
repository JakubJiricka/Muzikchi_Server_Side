import {AuthService} from "./auth.service";
import {BehaviorSubject} from "rxjs";

describe('AuthService', () => {

    it('logs user in', () => {
        let service = getService();
        spyOn(service['httpClient'], 'post').and.returnValue(new BehaviorSubject({data: 'response'}));

        service.login({foo: 'bar'});

        //calls backend with correct params
        expect(service['httpClient']['post']).toHaveBeenCalledWith('auth/login', {foo: 'bar'});
    });

    it('registers user', () => {
        let service = getService();
        spyOn(service['httpClient'], 'post').and.returnValue(new BehaviorSubject({data: 'response'}));

        service.register({foo: 'bar'});

        //calls backend with correct params
        expect(service['httpClient']['post']).toHaveBeenCalledWith('auth/register', {foo: 'bar'});
    });

    it('sends password reset link', () => {
        let service = getService();
        spyOn(service['httpClient'], 'post').and.returnValue(new BehaviorSubject({data: 'response'}));

        service.sendPasswordResetLink({foo: 'bar'});

        //calls backend with correct params
        expect(service['httpClient']['post']).toHaveBeenCalledWith('auth/password/email', {foo: 'bar'});
    });

    it('resets password', () => {
        let service = getService();
        spyOn(service['httpClient'], 'post').and.returnValue(new BehaviorSubject({data: 'response'}));

        service.resetPassword({foo: 'bar'});

        //calls backend with correct params
        expect(service['httpClient']['post']).toHaveBeenCalledWith('auth/password/reset', {foo: 'bar'});
    });

    it('returns redirect uri stored on current user', () => {
        let service = getService();
        service['currentUser']['redirectUri'] = 'foo';
        expect(service.getRedirectUri()).toEqual('foo');
    });

    it('returns agent redirect uri', () => {
        let service = getService();
        spyOn(service['currentUser'], 'hasPermission').and.returnValue(true);
        expect(service.getRedirectUri()).toEqual(service['agentRedirectUri']);
    });

    it('returns default redirect uri', () => {
        let service = getService();
        expect(service.getRedirectUri()).toEqual(service['redirectUri']);
    });

    function getService(): AuthService {
        return new AuthService(
            {post: () => {}} as any,
            {assignCurrent: () => {}, hasPermission: () => {}} as any,
            {navigate: () => {}} as any,
            {snapshot: {params: {token: 'token'}}} as any,
            {show: () => {}} as any,
            {run: (callback) => callback()} as any,
            {show: () => {}} as any,
        );
    }
});