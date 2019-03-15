import {KarmaTest} from "../../../../testing/karma-test";
import {LoginComponent} from "./login.component";
import {AuthService} from "../auth.service";
import {SocialAuthService} from "../social-auth.service";
import {fakeAsync, tick} from "@angular/core/testing";
import {SettingsService} from "../../shared/settings.service";
import {BehaviorSubject, Observable} from "rxjs";
import {LoadingIndicatorComponent} from "../../shared/loading-indicator/loading-indicator.component";

describe('LoginComponent', () => {

    let testBed: KarmaTest<any>;

    beforeEach(() => {
        testBed = new KarmaTest({
            module: {
                declarations: [LoginComponent, LoadingIndicatorComponent],
                providers: [AuthService, SocialAuthService],
            },
            component: LoginComponent
        });
    });

    it('sets initial component state', () => {
        expect(testBed.component.model).toEqual({remember: true});
        expect(testBed.component.errors).toEqual({});
    });

    it('logs user in', () => {
        spyOn(testBed.component.auth, 'login').and.returnValue(new BehaviorSubject({data: 'response'}));
        spyOn(testBed.component.user, 'assignCurrent');
        spyOn(testBed.component.router, 'navigate').and.returnValue(new Promise(resolve => resolve(true)));
        spyOn(testBed.component.auth, 'getRedirectUri').and.returnValue('foo bar');
        testBed.component.model = {foo: 'bar'};
        testBed.component.errors = {bar: 'foo'};
        expect(testBed.component.isLoading).toEqual(false);

        testBed.component.login();

        //calls backend
        expect(testBed.component.auth.login).toHaveBeenCalledWith({foo: 'bar'});

        //assigns current user
        expect(testBed.component.user.assignCurrent).toHaveBeenCalledWith('response');

        //navigates to success url
        expect(testBed.component.router.navigate).toHaveBeenCalledWith(['foo bar']);

        //sets and keeps component as loading
        expect(testBed.component.isLoading).toEqual(true);
    });

    it('handles login errors', () => {
        spyOn(testBed.component.auth, 'login').and.returnValue(Observable.throw({messages: {foo: 'bar'}}));
        spyOn(testBed.component.user, 'assignCurrent');

        testBed.component.login();

        //does not invoke success handler
        expect(testBed.component.user.assignCurrent).not.toHaveBeenCalled();

        //sets errors on component
        expect(testBed.component.errors).toEqual({foo: 'bar'});

        //sets "isLoading" to false
        expect(testBed.component.isLoading).toEqual(false);
    });

    it('fills in credentials and logs user in', fakeAsync(() => {
        spyOn(testBed.component, 'login');
        testBed.fixture.detectChanges();
        tick();
        expect(testBed.component.model.remember).toEqual(true);

        testBed.typeIntoEl('#email', 'foo@bar.com');
        testBed.typeIntoEl('#password', 'qux');
        testBed.toggleCheckbox('#remember');
        testBed.find('.submit-button').click();

        //calls login method
        expect(testBed.component.login).toHaveBeenCalledTimes(1);

        //inputs are bound to model properly
        expect(testBed.component.model.email).toEqual('foo@bar.com');
        expect(testBed.component.model.password).toEqual('qux');
        expect(testBed.component.model.remember).toEqual(false);
    }));

    it('renders logo and loading indicator', () => {
        testBed.get(SettingsService).set('branding.site_logo', 'foo_bar');
        testBed.fixture.detectChanges();
        expect(testBed.find('.logo')['href']).toEqual(testBed.get(SettingsService).get('base_url')+'/');
        expect(testBed.find('.logo img')['src']).toContain('foo_bar');
        expect(testBed.getChildComponent(LoadingIndicatorComponent)).toBeTruthy();
    });

    it('renders errors', () => {
        testBed.component.errors = {email: 'foo', password: 'bar', general: 'qux'};
        testBed.fixture.detectChanges();

        expect(testBed.find('.general-error').textContent).toEqual('qux');
        expect(testBed.find('.email-error').textContent).toEqual('foo');
        expect(testBed.find('.password-error').textContent).toEqual('bar');
    });

    it('links to forgot password and register page', () => {
        testBed.fixture.detectChanges();
        expect(testBed.find('.forgot-password')['href']).toContain('forgot-password');
        expect(testBed.find('.register-link')['href']).toContain('register');
    });

    it('renders social login icons', () => {
        spyOn(testBed.component.socialAuth, 'loginWith');
        spyOn(testBed.get(SettingsService), 'get').and.returnValue(false);
        testBed.fixture.detectChanges();

        //facebook
        expect(testBed.find('.social-icons .facebook img')['src']).toContain('social-icons/facebook.png');
        testBed.find('.social-icons .facebook').click();
        expect(testBed.component.socialAuth.loginWith).toHaveBeenCalledWith('facebook');

        //google
        expect(testBed.find('.social-icons .googleplus img')['src']).toContain('social-icons/google-plus.png');
        testBed.find('.social-icons .googleplus').click();
        expect(testBed.component.socialAuth.loginWith).toHaveBeenCalledWith('google');

        //twitter
        expect(testBed.find('.social-icons .twitter img')['src']).toContain('social-icons/twitter.png');
        testBed.find('.social-icons .twitter').click();
        expect(testBed.component.socialAuth.loginWith).toHaveBeenCalledWith('twitter');

        expect(testBed.component.socialAuth.loginWith).toHaveBeenCalledTimes(3);
    });

    it('hides envato social login icon if envato module is disabled', () => {
        spyOn(testBed.get(SettingsService), 'get').and.returnValue(false);

        testBed.fixture.detectChanges();

        expect(testBed.find('.social-icons .envato')).toBeFalsy();
    });
});