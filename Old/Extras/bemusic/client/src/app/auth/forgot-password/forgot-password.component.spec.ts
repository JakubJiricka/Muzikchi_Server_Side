import {KarmaTest} from "../../../../testing/karma-test";
import {AuthService} from "../auth.service";
import {fakeAsync, tick} from "@angular/core/testing";
import {SettingsService} from "../../shared/settings.service";
import {BehaviorSubject, Observable} from "rxjs";
import {ForgotPasswordComponent} from "./forgot-password.component";
import {LoadingIndicatorComponent} from "../../shared/loading-indicator/loading-indicator.component";

describe('ForgotPasswordComponent', () => {

    let testBed: KarmaTest<any>;

    beforeEach(() => {
        testBed = new KarmaTest({
            module: {
                declarations: [ForgotPasswordComponent, LoadingIndicatorComponent],
                providers: [AuthService],
            },
            component: ForgotPasswordComponent
        });
    });

    it('sets initial component state', () => {
        expect(testBed.component.model).toEqual({});
        expect(testBed.component.errors).toEqual({});
    });

    it('send reset password link to user', () => {
        spyOn(testBed.component.auth, 'sendPasswordResetLink').and.returnValue(new BehaviorSubject({data: 'response'}));
        spyOn(testBed.component.router, 'navigate');
        spyOn(testBed.component.toast, 'show');
        testBed.component.model = {foo: 'bar'};
        testBed.component.errors = {bar: 'foo'};
        expect(testBed.component.isLoading).toEqual(false);

        testBed.component.sendPasswordResetLink();

        //calls backend
        expect(testBed.component.auth.sendPasswordResetLink).toHaveBeenCalledWith({foo: 'bar'});

        //shows toast
        expect(testBed.component.toast.show).toHaveBeenCalledWith('response');

        //navigates to login page
        expect(testBed.component.router.navigate).toHaveBeenCalledWith(['/login']);

        //sets and keeps component as loading
        expect(testBed.component.isLoading).toEqual(true);
    });

    it('handles password reset errors', () => {
        spyOn(testBed.component.auth, 'sendPasswordResetLink').and.returnValue(Observable.throw({messages: {foo: 'bar'}}));
        spyOn(testBed.component.router, 'navigate');

        testBed.component.sendPasswordResetLink();

        //does not invoke success handler
        expect(testBed.component.router.navigate).not.toHaveBeenCalled();

        //sets errors on component
        expect(testBed.component.errors).toEqual({foo: 'bar'});

        //sets "isLoading" to false
        expect(testBed.component.isLoading).toEqual(false);
    });

    it('fills in credentials and sends password reset link', fakeAsync(() => {
        spyOn(testBed.component, 'sendPasswordResetLink');
        testBed.fixture.detectChanges();
        tick();

        testBed.typeIntoEl('#email', 'foo@bar.com');
        testBed.find('.submit-button').click();

        //calls sendPasswordResetLink method
        expect(testBed.component.sendPasswordResetLink).toHaveBeenCalledTimes(1);

        //inputs are bound to model properly
        expect(testBed.component.model.email).toEqual('foo@bar.com');
    }));

    it('renders logo and loading indicator', () => {
        testBed.get(SettingsService).set('branding.site_logo', 'foo_bar');
        testBed.fixture.detectChanges();
        expect(testBed.find('.logo')['href']).toEqual(testBed.get(SettingsService).get('base_url')+'/');
        expect(testBed.find('.logo img')['src']).toContain('foo_bar');
        expect(testBed.getChildComponent(LoadingIndicatorComponent)).toBeTruthy();
    });

    it('renders errors', () => {
        testBed.component.errors = {email: 'foo', general: 'qux'};
        testBed.fixture.detectChanges();

        expect(testBed.find('.general-error').textContent).toEqual('qux');
        expect(testBed.find('.email-error').textContent).toEqual('foo');
    });

    it('links to login page', () => {
        testBed.fixture.detectChanges();
        expect(testBed.find('.back-button')['href']).toContain('login');
    });
});