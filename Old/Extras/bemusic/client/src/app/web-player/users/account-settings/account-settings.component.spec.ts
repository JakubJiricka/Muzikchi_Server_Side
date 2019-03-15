import {KarmaTest} from "../../../../testing/karma-test";
import {AccountSettingsComponent} from "./account-settings.component";
import {CustomerNavbarComponent} from "../../shared/customer-navbar/customer-navbar.component";
import {UserService} from "../../admin/users/user.service";
import {BehaviorSubject} from "rxjs/BehaviorSubject";
import {ActivatedRouteStub} from "../../../../testing/stubs/activated-route-stub";
import {ActivatedRoute} from "@angular/router";
import {User} from "../../shared/models/User";
import {ToastService} from "../../../shared/toast/toast.service";
import {LoggedInUserWidgetComponent} from "../../shared/logged-in-user-widget/logged-in-user-widget.component";
import {fakeAsync, tick} from "@angular/core/testing";
import {UploadsService} from "../../../shared/uploads.service";
import {FileValidator} from "../../../shared/file-validator";
import {Observable} from "rxjs/Observable";
import {SocialAuthService} from "../../../auth/social-auth.service";
import {AuthService} from "../../../auth/auth.service";
import {ConnectSocialAccountsPanelComponent} from "./connect-social-accounts-panel/connect-social-accounts-panel.component";
import {EnvatoPurchasesPanelComponent} from "./envato-purchaes-panel/envato-purchases-panel.component";
import {CustomMenuComponent} from "../../../shared/custom-menu/custom-menu.component";

describe('AccountSettingsComponent', () => {
    let testBed: KarmaTest<AccountSettingsComponent>;
    let user: User;

    beforeEach(() => {
        testBed = new KarmaTest<AccountSettingsComponent>({
            module: {
                declarations: [
                    AccountSettingsComponent, CustomerNavbarComponent, LoggedInUserWidgetComponent,
                    ConnectSocialAccountsPanelComponent, EnvatoPurchasesPanelComponent, CustomMenuComponent
                ],
                providers: [
                    UserService, UploadsService, FileValidator, {provide: ActivatedRoute, useClass: ActivatedRouteStub},
                    SocialAuthService, AuthService,
                ],
            },
            component: AccountSettingsComponent
        });

        user = testBed.fake('User');
        testBed.get(ActivatedRoute)['testData'] = {resolves: {user, selects: {timezones: [{text: 'foo', value: 'bar'}], languages: ['en'], countries: ['foo']}}};
    });

    it('updates user account settings', fakeAsync(() => {
        spyOn(testBed.get(UserService), 'update').and.returnValue(new BehaviorSubject({}));
        spyOn(testBed.get(ToastService), 'show');
        testBed.component.errors.account = {first_name: 'foo'};
        testBed.fixture.detectChanges();
        tick();

        //fill in first and last name
        testBed.typeIntoEl('#first_name', 'foo');
        testBed.typeIntoEl('#last_name', 'bar');

        testBed.find('.account-settings-panel .submit-button').click();

        //updates user account settings
        expect(testBed.get(UserService).update).toHaveBeenCalledWith(user.id, jasmine.objectContaining({
            first_name: 'foo',
            last_name: 'bar'
        }));

        //shows toast after success
        expect(testBed.get(ToastService).show).toHaveBeenCalledWith(jasmine.any(String));

        //clears old errors
        expect(testBed.component.errors.account).toEqual({});
    }));

    it('shows user account settings errors', () => {
        let errors = {first_name: 'foo', last_name: 'bar'};
        spyOn(testBed.get(UserService), 'update').and.returnValue(Observable.throw({messages: errors}));
        spyOn(testBed.get(ToastService), 'show');

        testBed.find('.account-settings-panel .submit-button').click();
        testBed.fixture.detectChanges();

        //does not show success toast message
        expect(testBed.get(ToastService).show).not.toHaveBeenCalled();

        //sets errors on component
        expect(testBed.component.errors.account).toEqual(errors);

        //renders errors
        expect(testBed.find('.first-name-error').textContent.trim()).toEqual(errors.first_name);
        expect(testBed.find('.last-name-error').textContent.trim()).toEqual(errors.last_name);
    });

    it('uploads a new avatar for user', fakeAsync(() => {
        let updated = testBed.fake('User', 1, {avatar: 'foo.png'});
        spyOn(testBed.get(UploadsService), 'openUploadDialog').and.returnValue(new Promise(resolve => resolve('files')));
        spyOn(testBed.get(UserService), 'uploadAvatar').and.returnValue(new BehaviorSubject(updated));
        spyOn(testBed.get(ToastService), 'show');
        testBed.fixture.detectChanges();

        testBed.find('.avatar-upload-button').click();
        tick();

        //opens upload dialog
        expect(testBed.get(UploadsService).openUploadDialog).toHaveBeenCalledTimes(1);

        //uploads avatar
        expect(testBed.get(UserService).uploadAvatar).toHaveBeenCalledWith(user.id, 'files');

        //updates user model
        expect(testBed.component.user.avatar).toEqual(updated.avatar);

        //shows toast
        expect(testBed.get(ToastService).show).toHaveBeenCalledWith(jasmine.any(String));
    }));

    it('validates user avatar', fakeAsync(() => {
        spyOn(testBed.get(UploadsService), 'openUploadDialog').and.returnValue(new Promise(resolve => resolve('files')));
        spyOn(testBed.get(UploadsService), 'filesAreInvalid').and.returnValue(true);
        spyOn(testBed.get(UserService), 'uploadAvatar');
        testBed.fixture.detectChanges();

        testBed.find('.avatar-upload-button').click();
        tick();

        //opens upload dialog
        expect(testBed.get(UploadsService).openUploadDialog).toHaveBeenCalledTimes(1);

        //validates avatar
        expect(testBed.get(UploadsService).filesAreInvalid).toHaveBeenCalledTimes(1);

        //does not upload avatar
        expect(testBed.get(UserService).uploadAvatar).not.toHaveBeenCalled();
    }));

    it('deletes user avatar', () => {
        let updated = testBed.fake('User', 1, {avatar: 'foo.png'});
        spyOn(testBed.get(UserService), 'deleteAvatar').and.returnValue(new BehaviorSubject(updated));
        spyOn(testBed.get(ToastService), 'show');
        testBed.fixture.detectChanges();

        testBed.find('.avatar-remove-button').click();

        //deletes avatar
        expect(testBed.get(UserService).deleteAvatar).toHaveBeenCalledWith(user.id);

        //updates user model
        expect(testBed.component.user.avatar).toEqual(updated.avatar);

        //shows toast
        expect(testBed.get(ToastService).show).toHaveBeenCalledWith(jasmine.any(String));
    });

    it('changes user password', fakeAsync(() => {
        spyOn(testBed.get(UserService), 'changePassword').and.returnValue(new BehaviorSubject({}));
        spyOn(testBed.get(ToastService), 'show');
        testBed.component.errors.password = {new_password: 'foo'};
        testBed.fixture.detectChanges();
        tick();

        //fill in details
        testBed.typeIntoEl('#current_password', 'foo');
        testBed.typeIntoEl('#new_password', 'bar');
        testBed.typeIntoEl('#new_password_confirmation', 'baz');

        testBed.find('.change-password-panel .submit-button').click();
        testBed.fixture.detectChanges();
        tick();

        //changes password
        expect(testBed.get(UserService).changePassword).toHaveBeenCalledWith(user.id, {
            current_password: 'foo',
            new_password: 'bar',
            new_password_confirmation: 'baz'
        });

        //show toast message on success
        expect(testBed.get(ToastService).show).toHaveBeenCalledWith(jasmine.any(String));

        //clears old errors
        expect(testBed.component.errors.password).toEqual({});

        //clears input fields and password model
        expect(testBed.component.password).toEqual({});
        expect(testBed.find('#current_password')['value']).toBeFalsy();
    }));

    it('shows password change errors', () => {
        let errors = {current_password: 'foo', new_password: 'bar', new_password_confirmation: 'baz'};
        spyOn(testBed.get(UserService), 'changePassword').and.returnValue(Observable.throw({messages: errors}));
        spyOn(testBed.get(ToastService), 'show');

        testBed.find('.change-password-panel .submit-button').click();
        testBed.fixture.detectChanges();

        //does not show success toast message
        expect(testBed.get(ToastService).show).not.toHaveBeenCalled();

        //sets errors on components
        expect(testBed.component.errors.password).toEqual(errors);

        //renders errors
        expect(testBed.find('.current-password-error').textContent.trim()).toEqual(errors.current_password);
        expect(testBed.find('.new-password-error').textContent.trim()).toEqual(errors.new_password);
        expect(testBed.find('.new-password-confirmation-error').textContent.trim()).toEqual(errors.new_password_confirmation);
    });

    it('renders "ConnectSocialAccountsPanel" component', () => {
        testBed.fixture.detectChanges();
        expect(testBed.getChildComponent(ConnectSocialAccountsPanelComponent)).toBeTruthy();
    });

    it('updates user account preferences', fakeAsync(() => {
        spyOn(testBed.get(UserService), 'update').and.returnValue(new BehaviorSubject({}));
        spyOn(testBed.get(ToastService), 'show');
        testBed.fixture.detectChanges();
        tick();

        //fill in preferences
        testBed.chooseSelectValue('#timezone', 'bar');
        testBed.chooseSelectValue('#country', 'foo');
        testBed.chooseSelectValue('#language', 'en');

        testBed.find('.preferences-panel .submit-button').click();

        //updates user account preferences
        expect(testBed.get(UserService).update).toHaveBeenCalledWith(user.id, jasmine.objectContaining({
            timezone: 'bar', language: 'en', country: 'foo',
        }));
    }));

    it('shows user account preferences errors', () => {
        let errors = {country: 'foo', language: 'bar', timezone: 'baz'};
        spyOn(testBed.get(UserService), 'update').and.returnValue(Observable.throw({messages: errors}));

        testBed.find('.preferences-panel .submit-button').click();
        testBed.fixture.detectChanges();

        //renders errors
        expect(testBed.find('.country-error').textContent.trim()).toEqual(errors.country);
        expect(testBed.find('.language-error').textContent.trim()).toEqual(errors.language);
        expect(testBed.find('.timezone-error').textContent.trim()).toEqual(errors.timezone);
    });
});
