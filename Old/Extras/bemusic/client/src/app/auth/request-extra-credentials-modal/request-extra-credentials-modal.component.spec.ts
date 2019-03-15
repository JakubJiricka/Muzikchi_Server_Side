import {KarmaTest} from "../../../../testing/karma-test";
import {RequestExtraCredentialsModalComponent} from "./request-extra-credentials-modal.component";
import {fakeAsync, tick} from "@angular/core/testing";

describe('RequestExtraCredentialsModalComponent', () => {
    let testBed: KarmaTest<any>;

    beforeEach(() => {
        testBed = new KarmaTest({
            module: {
                declarations: [
                    RequestExtraCredentialsModalComponent
                ],
                providers: [],
            },
            component: RequestExtraCredentialsModalComponent,
        });
    });

    it('shows modal', () => {
        let onShowFired = false;
        testBed.component.onShow.subscribe(() => onShowFired = true);

        testBed.component.show({credentials: 'credentials'});

        //sets specified params on component instance
        expect(testBed.component.credentialsToRequest).toEqual('credentials');

        //calls super "show" method
        expect(onShowFired).toEqual(true);
    });

    it('closes modal via close icon', fakeAsync(() => {
        let onCloseFired = false;
        testBed.component.onClose.subscribe(() => onCloseFired = true);
        testBed.component.model = {foo: 'bar'};
        testBed.component.errors = {foo: 'bar'};
        testBed.component.credentialsToRequest = {foo: 'bar'};

        testBed.find('.close-button').click();

        //resets modal to initial state
        expect(testBed.component.model).toEqual({});
        expect(testBed.component.errors).toEqual({});
        expect(testBed.component.credentialsToRequest).toEqual([]);

        //calls super "close" method
        tick(200);
        expect(onCloseFired).toEqual(true);
    }));

    it('closes modal via "cancel" button', () => {
        spyOn(testBed.component, 'close');
        testBed.find('button.cancel').click();
        expect(testBed.component.close).toHaveBeenCalledTimes(1);
    });

    it('handles errors', () => {
        testBed.component.credentialsToRequest = ['email'];

        //pushes "password" into credentials to request
        testBed.component.handleErrors({messages: {email: 'foo'}});
        expect(testBed.component.credentialsToRequest).toEqual(['email', 'password']);

        //sets errors on components
        expect(testBed.component.errors).toEqual({email: 'foo'});

        //does not push into credentials to request unless error is for email
        testBed.component.handleErrors({messages: {foo: 'bar'}});
        expect(testBed.component.credentialsToRequest).toEqual(['email', 'password']);
    });

    it('renders email input container', fakeAsync(() => {
        testBed.component.credentialsToRequest = ['email'];
        testBed.fixture.detectChanges();
        tick();

        //only renders email input
        expect(testBed.findAll('.input-container').length).toEqual(1);
        expect(testBed.find('.email-address-container')).toBeTruthy();

        //binds to model properly
        testBed.typeIntoEl('#email', 'foo');
        expect(testBed.component.model.email).toEqual('foo');

        //shows errors
        testBed.component.errors = {email: 'foo'};
        testBed.fixture.detectChanges();
        expect(testBed.find('.email-address-container .error').textContent).toEqual('foo');
    }));

    it('renders password input container', fakeAsync(() => {
        testBed.component.credentialsToRequest = ['password'];
        testBed.fixture.detectChanges();
        tick();

        //only renders email input
        expect(testBed.findAll('.input-container').length).toEqual(1);
        expect(testBed.find('.password-container')).toBeTruthy();

        //binds to model properly
        testBed.typeIntoEl('#password', 'foo');
        expect(testBed.component.model.password).toEqual('foo');

        //shows errors
        testBed.component.errors = {password: 'foo'};
        testBed.fixture.detectChanges();
        expect(testBed.find('.password-container .error').textContent).toEqual('foo');
    }));

    it('submits extra credentials', () => {
        testBed.component.model = {foo: 'bar'};
        let onDoneFired: any = false;
        testBed.component.onDone.subscribe(data => onDoneFired = data);

        testBed.find('.submit-button').click();

        expect(onDoneFired).toEqual({foo: 'bar'});
    });
});