import {KarmaTest} from "../../../../testing/karma-test";
import {AuthService} from "../auth.service";
import {fakeAsync, tick} from "@angular/core/testing";
import {SettingsService} from "../../shared/settings.service";
import {BehaviorSubject, Observable} from "rxjs";
import {ResetPasswordComponent} from "./reset-password.component";
import {ActivatedRoute} from "@angular/router";
import {ActivatedRouteStub} from "../../../../testing/stubs/activated-route-stub";
import {LoadingIndicatorComponent} from "../../shared/loading-indicator/loading-indicator.component";

describe('ResetPasswordComponent', () => {
    let testBed: KarmaTest<any>;

    beforeEach(() => {
        testBed = new KarmaTest({
            module: {
                declarations: [ResetPasswordComponent, LoadingIndicatorComponent],
                providers: [AuthService, {provide: ActivatedRoute, useClass: ActivatedRouteStub}],
            },
            component: ResetPasswordComponent
        });
    });

    it('sets initial component state', () => {
        expect(testBed.component.model).toEqual({});
        expect(testBed.component.errors).toEqual({});
    });

    it('resets user password', () => {
        spyOn(testBed.component.auth, 'resetPassword').and.returnValue(new BehaviorSubject({data: 'response'}));
        spyOn(testBed.component.router, 'navigate');
        spyOn(testBed.component.toast, 'show');
        testBed.component.route.testParams = {token: 'foo token'};
        testBed.component.model = {foo: 'bar'};
        testBed.component.errors = {bar: 'foo'};
        expect(testBed.component.isLoading).toEqual(false);

        testBed.component.resetPassword();

        //calls backend with correct params
        expect(testBed.component.auth.resetPassword).toHaveBeenCalledWith({foo: 'bar', token: 'foo token'});

        //shows toast
        expect(testBed.component.toast.show).toHaveBeenCalledWith(jasmine.any(String));

        //navigates to help-center
        expect(testBed.component.router.navigate).toHaveBeenCalledWith(['/help-center']);

        //sets and keeps component as loading
        expect(testBed.component.isLoading).toEqual(true);
    });

    it('handles password reset errors', () => {
        spyOn(testBed.component.auth, 'resetPassword').and.returnValue(Observable.throw({messages: {foo: 'bar'}}));
        spyOn(testBed.component.router, 'navigate');

        testBed.component.resetPassword();

        //does not invoke success handler
        expect(testBed.component.router.navigate).not.toHaveBeenCalled();

        //sets errors on component
        expect(testBed.component.errors).toEqual({foo: 'bar'});

        //sets "isLoading" to false
        expect(testBed.component.isLoading).toEqual(false);
    });

    it('fills in credentials and resets password', fakeAsync(() => {
        spyOn(testBed.component, 'resetPassword');
        testBed.fixture.detectChanges();
        tick();

        testBed.typeIntoEl('#email', 'foo@bar.com');
        testBed.typeIntoEl('#password', 'qux');
        testBed.typeIntoEl('#password_confirmation', 'qux');
        testBed.find('.submit-button').click();

        //calls resetPassword method
        expect(testBed.component.resetPassword).toHaveBeenCalledTimes(1);

        //inputs are bound to model properly
        expect(testBed.component.model.email).toEqual('foo@bar.com');
        expect(testBed.component.model.password).toEqual('qux');
        expect(testBed.component.model.password_confirmation).toEqual('qux');
    }));

    it('renders logo and loading indicator', () => {
        testBed.get(SettingsService).set('branding.site_logo', 'foo_bar');
        testBed.fixture.detectChanges();
        expect(testBed.find('.logo')['href']).toEqual(testBed.get(SettingsService).get('base_url')+'/');
        expect(testBed.find('.logo img')['src']).toContain('foo_bar');
        expect(testBed.getChildComponent(LoadingIndicatorComponent)).toBeTruthy();
    });

    it('renders errors', () => {
        testBed.component.errors = {email: 'foo', password: 'bar'};
        testBed.fixture.detectChanges();

        expect(testBed.find('.email-error').textContent).toEqual('foo');
        expect(testBed.find('.password-error').textContent).toEqual('bar');
    });
});