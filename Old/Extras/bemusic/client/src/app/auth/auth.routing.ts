import {RouterModule} from '@angular/router';
import {LoginComponent} from "./login/login.component";
import {RegisterComponent} from "./register/register.component";
import {ResetPasswordComponent} from "./reset-password/reset-password.component";
import {GuestGuard} from "../guards/guest-guard.service";
import {ForgotPasswordComponent} from "./forgot-password/forgot-password.component";
import {DisableRouteGuard} from "../guards/disable-route-guard.service";

export const routing = RouterModule.forChild([
    {path: 'login', component: LoginComponent, canActivate: [GuestGuard]},
    {path: 'register', component: RegisterComponent, canActivate: [GuestGuard, DisableRouteGuard]},
    {path: 'forgot-password', component: ForgotPasswordComponent, canActivate: [GuestGuard]},
    {path: 'password/reset/:token', component: ResetPasswordComponent, canActivate: [GuestGuard]},
]);