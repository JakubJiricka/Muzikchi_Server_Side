import {Component, ViewEncapsulation} from '@angular/core';
import {AuthService} from "../auth.service";
import {ActivatedRoute, Router} from "@angular/router";
import {ToastService} from "../../shared/toast/toast.service";
import {CurrentUser} from "../current-user";
import {Settings} from "../../shared/settings.service";

@Component({
    selector: 'reset-password',
    templateUrl: './reset-password.component.html',
    styleUrls: ['./reset-password.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class ResetPasswordComponent {

    /**
     * Password reset credentials model.
     */
    public model: {email?: string, password?: string, password_confirmation?: string, token?: string} = {};

    /**
     * Errors returned from backend.
     */
    public errors: {email?: string, password?: string} = {};

    /**
     * Whether backend request is in progress currently.
     */
    public isLoading = false;

    /**
     * ResetPasswordComponent Constructor.
     */
    constructor(
        public auth: AuthService,
        public settings: Settings,
        private route: ActivatedRoute,
        private router: Router,
        private toast: ToastService,
        private currentUser: CurrentUser
    ) {}

    /**
     * Reset user password.
     */
    public resetPassword() {
        this.isLoading = true;
        this.model.token = this.route.snapshot.params['token'];

        this.auth.resetPassword(this.model).subscribe(response => {
            this.toast.show('Your password has been reset.');
            this.currentUser.assignCurrent(response.data);
            this.router.navigate([this.auth.getRedirectUri()]);
        }, response => {
            this.errors = response['messages'];
            this.isLoading = false;
        });
    }
}
