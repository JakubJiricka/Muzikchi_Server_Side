import {ConnectSocialAccountsPanelComponent} from "./connect-social-accounts-panel.component";
import {BehaviorSubject} from "rxjs/BehaviorSubject";
import {fakeAsync, tick} from "@angular/core/testing";
import {KarmaTest} from "../../../../../testing/karma-test";
import {SocialAuthService} from "../../../auth/social-auth.service";
import {AuthService} from "../../../auth/auth.service";
import {ToastService} from "../../../shared/toast/toast.service";
import {SettingsService} from "../../../shared/settings.service";

describe('ConnectSocialAccountPanelComponent', () => {
    let testBed: KarmaTest<ConnectSocialAccountsPanelComponent>;

    beforeEach(() => {
        testBed = new KarmaTest<ConnectSocialAccountsPanelComponent>({
            module: {
                declarations: [
                   ConnectSocialAccountsPanelComponent
                ],
                providers: [SocialAuthService, AuthService],
            },
            component: ConnectSocialAccountsPanelComponent
        });
    });

    it('renders envato row when account is connected', () => {
        testBed.get(SettingsService).set('envato.enable', 1);
        testSocialLoginRowWhenAccountIsConnected('envato');
    });

    it('renders google row when account is connected', () => {
        testSocialLoginRowWhenAccountIsConnected('google');
    });

    it('renders facebook row when account is connected', () => {
        testSocialLoginRowWhenAccountIsConnected('facebook');
    });

    it('renders twitter row when account is connected', () => {
        testSocialLoginRowWhenAccountIsConnected('twitter');
    });

    it('renders envato row when account is not connected', fakeAsync(() => {
        testBed.get(SettingsService).set('envato.enable', 1);
        testSocialLoginRowWhenAccountIsNotConnected('envato');
    }));

    it('renders google row when account is not connected', fakeAsync(() => {
        testSocialLoginRowWhenAccountIsNotConnected('google');
    }));

    it('renders facebook row when account is not connected', fakeAsync(() => {
        testSocialLoginRowWhenAccountIsNotConnected('facebook');
    }));

    it('renders twitter row when account is not connected', fakeAsync(() => {
        testSocialLoginRowWhenAccountIsNotConnected('twitter');
    }));

    function testSocialLoginRowWhenAccountIsConnected(name: string) {
        spyOn(testBed.get(SocialAuthService), 'disconnect').and.returnValue(new BehaviorSubject({}));
        spyOn(testBed.get(ToastService), 'show');
        let socialProfile = testBed.fake('SocialProfile', 1, {service_name: name});
        testBed.component.user = testBed.fake('User', 1, {social_profiles: [socialProfile]});
        testBed.fixture.detectChanges();

        //renders social account username
        expect(testBed.find('.'+name+'-row .social-account-username').textContent.trim()).toEqual(socialProfile.username);

        //renders "disable" button for disconnecting service
        expect(testBed.find('.'+name+'-row .disable-button')).toBeTruthy();

        //disconnects social account
        testBed.find('.'+name+'-row .disable-button').click();
        expect(testBed.get(SocialAuthService).disconnect).toHaveBeenCalledWith(name);
        expect(testBed.get(ToastService).show).toHaveBeenCalledWith(jasmine.stringMatching(name));
        expect(testBed.component.user.social_profiles).toEqual([]);

        //hides "enable" button for connect service, if it's already connected
        expect(testBed.find('.'+name+'-row .enable-button')).toBeFalsy();
    }

    function testSocialLoginRowWhenAccountIsNotConnected(name: string) {
        let updatedUser = testBed.fake('User', 1, {social_profiles: [{id: 1, service_name: 'foo', username: 'bar'}]});
        spyOn(testBed.get(SocialAuthService), 'connect').and.returnValue(new Promise(resolve => resolve(updatedUser)));
        spyOn(testBed.get(ToastService), 'show');
        testBed.component.user = testBed.fake('User', 1);
        testBed.fixture.detectChanges();

        //does not render social account username
        expect(testBed.find('.'+name+'-row .social-account-username')).toBeFalsy();

        //hides "disable" button for disconnecting service
        expect(testBed.find('.'+name+'-row .disable-button')).toBeFalsy();

        //renders 'enable' button for connecting social account
        expect(testBed.find('.'+name+'-row .enable-button')).toBeTruthy();

        //connects social account
        testBed.find('.'+name+'-row .enable-button').click();
        tick();
        expect(testBed.get(SocialAuthService).connect).toHaveBeenCalledWith(name);
        expect(testBed.get(ToastService).show).toHaveBeenCalledWith(jasmine.stringMatching(name));
        expect(testBed.component.user.social_profiles).toEqual(updatedUser.social_profiles);
    }
});
