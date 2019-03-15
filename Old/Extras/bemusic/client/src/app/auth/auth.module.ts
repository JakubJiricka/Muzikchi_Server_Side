import {NgModule} from '@angular/core';
import {routing} from "./auth.routing";
import {LoginComponent} from "./login/login.component";
import {RegisterComponent} from "./register/register.component";
import {ResetPasswordComponent} from "./reset-password/reset-password.component";
import {AuthService} from "./auth.service";
import {SocialAuthService} from "./social-auth.service";
import {ForgotPasswordComponent} from "./forgot-password/forgot-password.component";
import {RequestExtraCredentialsModalComponent} from "./request-extra-credentials-modal/request-extra-credentials-modal.component";
import {ModalService} from "../shared/modal/modal.service";
import {SharedModule} from "../shared/shared.module";
import {GuestGuard} from "../guards/guest-guard.service";

@NgModule({
    imports: [
        SharedModule,
        routing
    ],
    declarations: [
        LoginComponent,
        RegisterComponent,
        ForgotPasswordComponent,
        ResetPasswordComponent,
        RequestExtraCredentialsModalComponent,
    ],
    entryComponents: [
        RequestExtraCredentialsModalComponent,
    ],
    exports:      [],
    providers:    [
        AuthService,
        SocialAuthService,
        ModalService,
        GuestGuard,
    ]
})
export class AuthModule { }