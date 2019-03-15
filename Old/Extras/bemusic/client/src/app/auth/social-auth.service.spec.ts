import {SocialAuthService} from "./social-auth.service";
import {RequestExtraCredentialsModalComponent} from "./request-extra-credentials-modal/request-extra-credentials-modal.component";
import {BehaviorSubject, Observable} from "rxjs";

describe('SocialAuthService', () => {

    it('listens for "message" event on window', () => {
        spyOn(window, 'addEventListener');

        getService();

        expect(window.addEventListener).toHaveBeenCalledWith('message', jasmine.any(Function), false);
    });

    it('shows new social login window', () => {
        spyOn(window, 'open');
        let service = getService();

        let promise = service.loginWith('facebook');

        //returns promise
        expect(promise).toEqual(jasmine.any(Promise));

        //opens new window for social login
        let left = (screen.width/2)-(service['windowWidth']/2);
        let top  = (screen.height/2)-(service['windowHeight']/2);
        expect(window.open).toHaveBeenCalledWith(
            jasmine.any(String),
            jasmine.any(String),
            'menubar=0, location=0, toolbar=0, titlebar=0, status=0, scrollbars=1, width='+service['windowWidth']+', height='+service['windowHeight']+', '+'left='+left+', top='+top
        )
    });

    it('handles social login callback', () => {
        let service = getService();

        //does not error out if there's no status
        service.socialLoginCallback(null);

        //redirects user on "SUCCESS"
        spyOn(service['auth'], 'getRedirectUri').and.returnValue('foo uri');
        spyOn(service['router'], 'navigate');
        spyOn(service['currentUser'], 'assignCurrent');
        service.socialLoginCallback('SUCCESS', {user: 'foo'});
        expect(service['currentUser']['assignCurrent']).toHaveBeenCalledWith('foo');
        expect(service['auth']['getRedirectUri']).toHaveBeenCalledTimes(1);
        expect(service['router']['navigate']).toHaveBeenCalledWith(['foo uri']);

        //redirects user on "ALREADY_LOGGED_IN"
        service.socialLoginCallback('ALREADY_LOGGED_IN');
        expect(service['auth']['getRedirectUri']).toHaveBeenCalledTimes(2);
        expect(service['router']['navigate']).toHaveBeenCalledTimes(2);
        expect(service['router']['navigate']).toHaveBeenCalledWith(['foo uri']);

        //requests extra credentials on "REQUEST_EXTRA_CREDENTIALS"
        spyOn(service, 'showRequestExtraCredentialsModal');
        service.socialLoginCallback('REQUEST_EXTRA_CREDENTIALS', {foo: 'bar'});
        expect(service.showRequestExtraCredentialsModal).toHaveBeenCalledWith({credentials: {foo: 'bar'}});

        //shows error on "ERROR"
        spyOn(service['toast'], 'show');
        service.socialLoginCallback('ERROR', {foo: 'bar'});
        expect(service['toast']['show']).toHaveBeenCalledWith({foo: 'bar'}, {delay: jasmine.any(Number), type: 'error'});

        //shows default error if no error messages specified
        service.socialLoginCallback('error');
        expect(service['toast']['show']).toHaveBeenCalledTimes(2);
        expect(service['toast']['show']).toHaveBeenCalledWith(jasmine.anything(), {delay: jasmine.any(Number), type: 'error'});
    });

    it('shows request extra credentials modal', () => {
        let service = getService(),
            modal: any = {onDone: new BehaviorSubject('done data'), onClose: new BehaviorSubject(null)};
        spyOn(service['modal'], 'show').and.returnValue(modal);
        spyOn(service, 'sendExtraCredentialsToBackend');
        spyOn(service['auth'], 'setErrors' as any);

        service.showRequestExtraCredentialsModal({foo: 'bar'});

        //opens modal
        expect(service['modal']['show']).toHaveBeenCalledWith(RequestExtraCredentialsModalComponent, {foo: 'bar'});

        //sets modal instance on service
        expect(service['extraCredentialsModal']).toEqual(modal);

        //sends extra credentials to backend on modal "onDone" event
        expect(service.sendExtraCredentialsToBackend).toHaveBeenCalledWith('done data');
    });

    it('sends extra credentials to backend and handles success', () => {
        let service = getService();
        service['extraCredentialsModal'] = {close: () => {}, handleErrors: () => {}} as any;
        spyOn(service['httpClient'], 'post').and.returnValue(new BehaviorSubject({data: 'foo'}));
        spyOn(service['auth'], 'getRedirectUri').and.returnValue('foo uri');
        spyOn(service['router'], 'navigate');
        spyOn(service['extraCredentialsModal'], 'close');
        spyOn(service['extraCredentialsModal'], 'handleErrors');

        service.sendExtraCredentialsToBackend({foo: 'bar'});

        //calls backend
        expect(service['httpClient']['post']).toHaveBeenCalledWith('auth/social/extra-credentials', {foo: 'bar'});

        //redirects after success
        expect(service['auth']['getRedirectUri']).toHaveBeenCalledTimes(1);
        expect(service['router']['navigate']).toHaveBeenCalledWith(['foo uri']);

        //closes extra credentials modal
        expect(service['extraCredentialsModal']['close']).toHaveBeenCalledTimes(1);

        //does not call "handleErrors" if request was successful
        expect(service['extraCredentialsModal']['handleErrors']).not.toHaveBeenCalled();
    });

    it('sends extra credentials to backend and handles error', () => {
        let service = getService();
        service['extraCredentialsModal'] = {close: () => {}, handleErrors: () => {}} as any;
        spyOn(service['httpClient'], 'post').and.returnValue(Observable.throw({foo: 'bar'}));
        spyOn(service['extraCredentialsModal'], 'handleErrors');

        service.sendExtraCredentialsToBackend({foo: 'bar'});

        expect(service['extraCredentialsModal']['handleErrors']).toHaveBeenCalledTimes(1);
    });

    function getService(): SocialAuthService {
        return new SocialAuthService(
            {post: () => {}} as any,
            {assignCurrent: () => {}} as any,
            {navigate: () => {}} as any,
            {get: () => 'foo'} as any,
            {show: () => {}} as any,
            {getRedirectUri: () => {}, setErrors: () => {}} as any,
            {show: () => {}} as any,
            {t: () => {}} as any,
        );
    }
});