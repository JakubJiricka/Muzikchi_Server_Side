import {APP_INITIALIZER, ErrorHandler, NgModule} from '@angular/core';
import {AppComponent} from './app.component';
import {AppRoutingModule} from "./app-routing.module";
import {WebPlayerModule} from "./web-player/web-player.module";
import {SharedModule} from "./shared/shared.module";
import {Bootstrapper} from "./bootstrapper.service";
import {AuthModule} from "./auth/auth.module";
import {BrowserModule} from "@angular/platform-browser";
import {UrlSerializer} from "@angular/router";
import {CustomUrlSerializer} from "./shared/custom-url-serializer";
import {HTTP_INTERCEPTORS} from "@angular/common/http";
import {XsrfInterceptor} from "./shared/xsrf-interceptor.service";
import {Settings} from "./shared/settings.service";
import {RavenErrorHandler} from "./raven-error-handler";
import {WildcardRoutingModule} from "./wildcard-routing.module";
import {CurrentUser} from "./auth/current-user";

export function init_app(bootstrapper: Bootstrapper) {
    return () => bootstrapper.bootstrap();
}

export function errorHandlerFactory (settings: Settings, currentUser: CurrentUser) {
    return new RavenErrorHandler(settings, currentUser);
}

@NgModule({
    declarations: [
        AppComponent,
    ],
    imports: [
        BrowserModule,
        SharedModule.forRoot(),
        AppRoutingModule,
        AuthModule,
        WebPlayerModule,
        WildcardRoutingModule,
    ],
    providers: [
        Bootstrapper,
        {provide: UrlSerializer, useClass: CustomUrlSerializer},
        {
            provide: HTTP_INTERCEPTORS,
            useClass: XsrfInterceptor,
            multi: true,
        },
        { provide: ErrorHandler,
            useFactory: errorHandlerFactory,
            deps: [Settings, CurrentUser],
        },
        {
            provide: APP_INITIALIZER,
            useFactory: init_app,
            deps: [Bootstrapper],
            multi: true,
        },
    ],
    bootstrap: [AppComponent]
})
export class AppModule {
}
