import {KarmaTest} from "../../../../testing/karma-test";
import {AuthService} from "../auth.service";
import {SocialAuthService} from "../social-auth.service";
import {fakeAsync, tick} from "@angular/core/testing";
import {SettingsService} from "../../shared/settings.service";
import {BehaviorSubject, Observable} from "rxjs";
import {RegisterComponent} from "./register.component";
import {LoadingIndicatorComponent} from "../../shared/loading-indicator/loading-indicator.component";

describe('RegisterComponent', () => {

    let testBed: KarmaTest<any>;

    beforeEach(() => {
        testBed = new KarmaTest({
            module: {
                declarations: [RegisterComponent, LoadingIndicatorComponent],
                providers: [AuthService, SocialAuthService],
            },
            component: RegisterComponent
        });
    });

    it('sets initial component state', () => {
        expect(testBed.component.model).toEqual({});
        expect(testBed.component.errors).toEqual({});
    });

    it('registers a user', () => {
        spyOn(testBed.component.auth, 'register').and.returnValue(new BehaviorSubject({data: 'response'}));
        spyOn(testBed.component.user, 'assignCurrent');
        spyOn(testBed.component.router, 'navigate');
        spyOn(testBed.component.auth, 'getRedirectUri').and.returnValue('foo bar');
        testBed.component.model = {foo: 'bar'};
        testBed.component.errors = {bar: 'foo'};
        expect(testBed.component.isLoading).toEqual(false);

        testBed.component.register();

        //calls backend
        expect(testBed.component.auth.register).toHaveBeenCalledWith({foo: 'bar'});

        //assigns current user
        expect(testBed.component.user.assignCurrent).toHaveBeenCalledWith('response');

        //navigates to success url
        expect(testBed.component.router.navigate).toHaveBeenCalledWith(['foo bar']);

        //sets and keeps component as loading
        expect(testBed.component.isLoading).toEqual(true);
    });

    it('handles register errors', () => {
        spyOn(testBed.component.auth, 'register').and.returnValue(Observable.throw({messages: {foo: 'bar'}}));
        spyOn(testBed.component.user, 'assignCurrent');

        testBed.component.register();

        //does not invoke success handler
        expect(testBed.component.user.assignCurrent).not.toHaveBeenCalled();

        //sets errors on component
        expect(testBed.component.errors).toEqual({foo: 'bar'});

        //sets "isLoading" to false
        expect(testBed.component.isLoading).toEqual(false);
    });

    it('fills in credentials and registers user', fakeAsync(() => {
        spyOn(testBed.component, 'register');
        spyOn(testBed.component.settings, 'envatoPurchaseCodeIsRequired').and.returnValue(true);
        testBed.fixture.detectChanges();
        tick();

        testBed.typeIntoEl('#email', 'foo@bar.com');
        testBed.typeIntoEl('#password', 'foo password');
        testBed.typeIntoEl('#password_confirmation', 'foo password confirmation');
        testBed.typeIntoEl('#purchase_code', 'foo purchase code');
        testBed.find('.register-button').click();

        //inputs are bound to model properly
        expect(testBed.component.model.email).toEqual('foo@bar.com');
        expect(testBed.component.model.password).toEqual('foo password');
        expect(testBed.component.model.purchase_code).toEqual('foo purchase code');

        //calls register method
        expect(testBed.component.register).toHaveBeenCalledTimes(1);
    }));

    it('hides purchase code input if envato module is disabled', () => {
        spyOn(testBed.component.settings, 'envatoPurchaseCodeIsRequired').and.returnValue(false);
        testBed.fixture.detectChanges();
        expect(testBed.find('#purchase_code')).toBeFalsy();
    });

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

    it('links to login page', () => {
        testBed.fixture.detectChanges();
        expect(testBed.find('.login-link')['href']).toContain('login');
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
});