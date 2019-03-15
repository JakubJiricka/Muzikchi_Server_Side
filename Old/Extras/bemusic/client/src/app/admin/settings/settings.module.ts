import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {SettingsComponent} from "./settings.component";
import {SettingsPanelComponent} from "./settings-panel.component";
import {AuthenticationSettingsComponent} from "./authentication/authentication-settings.component";
import {CacheSettingsComponent} from "./cache/cache-settings.component";
import {EnvatoSettingsComponent} from "./envato/envato-settings.component";
import {PermissionsSettingsComponent} from "./permissions/permissions-settings.component";
import {AnalyticsSettingsComponent} from "./analytics/analytics-settings.component";
import {LocalizationSettingsComponent} from "./localization/localization-settings.component";
import {MailSettingsComponent} from "./mail/mail-settings.component";
import {LoggingSettingsComponent} from "./logging/logging-settings.component";
import {QueueSettingsComponent} from "./queue/queue-settings.component";
import {ProvidersSettingsComponent} from "./providers/providers-settings.component";
import {PlayerSettingsComponent} from "./player/player-settings.component";
import {GenresSettingsComponent} from "./genres/genres-settings.component";
import {BlockedArtistsSettingsComponent} from "./blocked-artists/blocked-artists-settings.component";
import {SharedModule} from "../../shared/shared.module";
import {SettingsResolve} from "./settings-resolve.service";
import {SettingsState} from "./settings-state.service";
import {GeneralSettingsComponent} from "./general/general-settings.component";

@NgModule({
    imports: [
        SharedModule,
    ],
    declarations: [
        SettingsComponent,
        SettingsPanelComponent,
        AuthenticationSettingsComponent,
        CacheSettingsComponent,
        EnvatoSettingsComponent,
        PermissionsSettingsComponent,
        AnalyticsSettingsComponent,
        LocalizationSettingsComponent,
        MailSettingsComponent,
        LoggingSettingsComponent,
        QueueSettingsComponent,
        ProvidersSettingsComponent,
        PlayerSettingsComponent,
        GenresSettingsComponent,
        BlockedArtistsSettingsComponent,
        GeneralSettingsComponent,
    ],
    providers: [
        SettingsResolve,
        SettingsState,
    ]
})
export class SettingsModule {
}
