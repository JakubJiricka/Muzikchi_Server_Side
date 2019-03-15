import {Injectable} from '@angular/core';
import {Router} from '@angular/router';
import {CurrentUser} from "./current-user";
import {ToastService} from "../shared/toast/toast.service";
import {AuthService} from "./auth.service";
import {ModalService} from "../shared/modal/modal.service";
import {RequestExtraCredentialsModalComponent} from "./request-extra-credentials-modal/request-extra-credentials-modal.component";
import {Translations} from "../shared/translations/translations.service";
import {AppHttpClient} from "../shared/app-http-client.service";
import {Settings} from "../shared/settings.service";
import {User} from "../shared/types/models/User";

@Injectable()
export class SocialAuthService {

    /**
     * Instance of extraCredentialsModal.
     */
    private extraCredentialsModal: RequestExtraCredentialsModalComponent;

    /**
     * Social login popup window height.
     */
    private windowHeight = 500;

    /**
     * Social login popup window width.
     */
    private windowWidth = 600;

    /**
     * Resolve for latest social login or connect call.
     */
    private resolve: Function;

    /**
     * SocialAuthService Constructor.
     */
    constructor(
        protected httpClient: AppHttpClient,
        public currentUser: CurrentUser,
        protected router: Router,
        public settings: Settings,
        protected toast: ToastService,
        protected auth: AuthService,
        protected modal: ModalService,
        private i18n: Translations,
    ) {
        this.listenForMessageFromPopup();
    }

    /**
     * Log user in with specified social account.
     */
    public loginWith(serviceName: string): Promise<User> {
        return this.openNewSocialAuthWindow('secure/auth/social/'+serviceName+'/login') as any;
    }

    /**
     * Connect specified social account to current user.
     */
    public connect(serviceName: string): Promise<User> {
        return this.openNewSocialAuthWindow('secure/auth/social/'+serviceName+'/connect') as any;
    }

    /**
     * Disconnect specified social account from current user.
     */
    public disconnect(serviceName: string) {
        return this.httpClient.post('auth/social/'+serviceName+'/disconnect');
    }

    /**
     * Handle social login callback, based on returned status.
     */
    public socialLoginCallback(status: string, data = null) {
        if ( ! status) return;

        switch(status.toUpperCase()) {
            case 'SUCCESS':
                this.currentUser.assignCurrent(data['user']);
                this.router.navigate([this.auth.getRedirectUri()]);
                break;
            case 'SUCCESS_CONNECTED':
                this.resolve && this.resolve(data.user);
                break;
            case 'ALREADY_LOGGED_IN':
                this.router.navigate([this.auth.getRedirectUri()]);
                break;
            case 'REQUEST_EXTRA_CREDENTIALS':
                this.showRequestExtraCredentialsModal({credentials: data});
                break;
            case 'ERROR':
                let message = data ? data : this.i18n.t('An error occurred. Please try again later');
                this.toast.show(message, {delay: 6000, type: 'error'});
        }
    };

    /**
     * Open extra credentials modal and subscribe to modal events.
     */
    public showRequestExtraCredentialsModal(config: Object) {
        this.extraCredentialsModal = this.modal.show(RequestExtraCredentialsModalComponent, config);
        this.extraCredentialsModal.onDone.subscribe(credentials => this.sendExtraCredentialsToBackend(credentials));
    }

    /**
     * Send specified credentials to backend and handle success/error.
     */
    public sendExtraCredentialsToBackend(data: Object) {
        this.httpClient.post('auth/social/extra-credentials', data).subscribe(response => {
            this.currentUser.assignCurrent(response['data']);
            this.router.navigate([this.auth.getRedirectUri()]);
            this.extraCredentialsModal.close();
        }, this.extraCredentialsModal.handleErrors.bind(this.extraCredentialsModal));
    }

    /**
     * Open new browser window with given url.
     */
    private openNewSocialAuthWindow(url: string) {
        let left = (screen.width/2)-(this.windowWidth/2);
        let top  = (screen.height/2)-(this.windowHeight/2);

        return new Promise(resolve => {
            this.resolve = resolve;

            window.open(
                url,
                'Authenticate Account',
                'menubar=0, location=0, toolbar=0, titlebar=0, status=0, scrollbars=1, width='+this.windowWidth+', height='+this.windowHeight+', '+'left='+left+', top='+top
            );
        });
    }

    /**
     * Listen for "message" event from social login popup
     * window and call "socialLoginCallback" once received
     */
    private listenForMessageFromPopup() {
         window.addEventListener('message', e => {
             if (this.settings.getBaseUrl().indexOf(e.origin) === -1) return;
             this.socialLoginCallback(e.data.status, e.data.callbackData);
         }, false);
    }
}