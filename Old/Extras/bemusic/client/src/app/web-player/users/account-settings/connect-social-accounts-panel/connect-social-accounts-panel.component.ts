import {Component, Input, ViewEncapsulation} from '@angular/core';
import {ToastService} from "../../../../shared/toast/toast.service";
import {User} from "../../../../shared/types/models/User";
import {SocialAuthService} from "../../../../auth/social-auth.service";
import {Settings} from "../../../../shared/settings.service";

@Component({
    selector: 'connect-social-accounts-panel',
    templateUrl: './connect-social-accounts-panel.component.html',
    styleUrls: ['./connect-social-accounts-panel.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class ConnectSocialAccountsPanelComponent {

    /**
     * User model.
     */
    @Input() public user = new User();

    /**
     * ConnectSocialAccountsPanelComponent Constructor.
     */
    constructor(
        private social: SocialAuthService,
        private toast: ToastService,
        public settings: Settings,
    ) {}

    /**
     * Connect specified social account to user.
     */
    public connectSocialAccount(name: string) {
        this.social.connect(name).then(user => {
            this.user.social_profiles = user.social_profiles;
            this.toast.show('Connected: '+name);
        });
    }

    /**
     * Disconnect specified social account from user.
     */
    public disconnectSocialAccount(name: string) {
        this.social.disconnect(name).subscribe(() => {
            this.toast.show('Disconnected: '+name);
            let i = this.user.social_profiles.findIndex(s => s.service_name === name);
            this.user.social_profiles.splice(i, 1);
        });
    }

    /**
     * Get username from specified social account model.
     */
    public getSocialAccountUsername(name: string): string {
        if ( ! this.user.social_profiles) return;

        let account = this.user.social_profiles
            .find(social => social.service_name === name);

        return account && account.username;
    }
}