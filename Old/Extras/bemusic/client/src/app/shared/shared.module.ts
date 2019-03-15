import {ModuleWithProviders, NgModule} from '@angular/core';
import {RouterModule} from "@angular/router";
import {HttpClientModule} from "@angular/common/http";
import {Settings} from "./settings.service";
import {AppHttpClient} from "./app-http-client.service";
import {Translations} from "./translations/translations.service";
import {TranslateDirective} from "./translations/translate.directive";
import {CurrentUser} from "../auth/current-user";
import {ToastComponent} from "./toast/toast.component";
import {ConfirmModalComponent} from "./modal/confirm-modal/confirm-modal.component";
import {SvgIconComponent} from "./svg-icon/svg-icon.component";
import {FormsModule, ReactiveFormsModule} from "@angular/forms";
import {LoadingIndicatorComponent} from "./loading-indicator/loading-indicator.component";
import {TooltipDirective} from "./tooltip/tooltip.directive";
import {NoResultsMessageComponent} from "./no-results-message/no-results-message.component";
import {CommonModule} from "@angular/common";
import {CustomMenuComponent} from "./custom-menu/custom-menu.component";
import {utils} from "./utils";
import {ToastService} from "./toast/toast.service";
import {ModalPlaceholderService} from "./modal/modal-placeholder.service";
import {LocalStorage} from "./local-storage.service";
import {InfiniteScrollDirective} from "./infinite-scroll/infinite-scroll.directive";
import {DropdownComponent} from "./dropdown/dropdown.component";
import {DropdownService} from "./dropdown/dropdown.service";
import {BrowserEvents} from "./browser-events.service";
import {AuthGuard} from "../guards/auth-guard.service";
import { EmptyRouteComponent } from './empty-route/empty-route.component';
import {ReorderDirective} from "./reorder.directive";
import {Uploads} from "./uploads.service";
import {UploadFileModalComponent} from "./upload-file-modal/upload-file-modal.component";
import {FileDropzoneDirective} from "./file-dropzone/file-dropzone.directive";
import {FileValidator} from "./file-validator";
import {ValueLists} from "./value-lists.service";
import {HttpCacheClient} from "./http-cache-client";
import {PageComponent} from "../pages/page.component";
import {ColorPicker} from "./colorpicker/colorpicker.service";
import {MapToIterable} from "./map-to-iterable";
import {PreviewApp} from "./preview-app.service";
import { EnterKeybindDirective } from './enter-keybind.directive';
import {Pages} from "../admin/pages/pages.service";
import {DisableRouteGuard} from "../guards/disable-route-guard.service";
import {TitleService} from "./title.service";
import {HttpErrorHandler} from "./http-error-handler.service";
import {Localizations} from "./translations/localizations.service";
import { LazyLoadImageModule } from 'ng-lazyload-image';
import {CheckPermissionsGuard} from "../guards/check-permissions-guard.service";
import {CustomHomepage} from "./custom-homepage.service";

@NgModule({
    imports: [
        CommonModule,
        FormsModule,
        ReactiveFormsModule,
        RouterModule,
        HttpClientModule,
        LazyLoadImageModule
    ],
    exports: [
        CommonModule,
        FormsModule,
        ReactiveFormsModule,
        RouterModule,
        HttpClientModule,
        LazyLoadImageModule,
        TranslateDirective,
        ToastComponent,
        SvgIconComponent,
        ConfirmModalComponent,
        LoadingIndicatorComponent,
        TooltipDirective,
        NoResultsMessageComponent,
        CustomMenuComponent,
        InfiniteScrollDirective,
        DropdownComponent,
        ReorderDirective,
        FileDropzoneDirective,
        MapToIterable,
        EnterKeybindDirective,
    ],
    declarations: [
        TranslateDirective,
        ToastComponent,
        SvgIconComponent,
        ConfirmModalComponent,
        LoadingIndicatorComponent,
        TooltipDirective,
        NoResultsMessageComponent,
        CustomMenuComponent,
        InfiniteScrollDirective,
        DropdownComponent,
        EmptyRouteComponent,
        ReorderDirective,
        UploadFileModalComponent,
        FileDropzoneDirective,
        PageComponent,
        MapToIterable,
        EnterKeybindDirective,
    ],
    entryComponents: [
        ConfirmModalComponent,
        UploadFileModalComponent
    ]
})
export class SharedModule {
    static forRoot(): ModuleWithProviders {
        return {
            ngModule: SharedModule,
            providers: [
                Settings,
                AppHttpClient,
                HttpCacheClient,
                HttpErrorHandler,
                Translations,
                Localizations,
                CurrentUser,
                utils,
                ToastService,
                ModalPlaceholderService,
                LocalStorage,
                DropdownService,
                BrowserEvents,
                Uploads,
                AuthGuard,
                FileValidator,
                ValueLists,
                ColorPicker,
                PreviewApp,
                Pages,
                DisableRouteGuard,
                TitleService,
                CheckPermissionsGuard,
                CustomHomepage,
            ]
        };
    }
}

