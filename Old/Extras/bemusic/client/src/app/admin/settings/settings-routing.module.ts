import {Route} from '@angular/router';
import {SettingsComponent} from "./settings.component";
import {SettingsResolve} from "./settings-resolve.service";
import {AuthenticationSettingsComponent} from "./authentication/authentication-settings.component";
import {CacheSettingsComponent} from "./cache/cache-settings.component";
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
import {LocalizationsResolve} from "../translations/localizations-resolve.service";
import {GeneralSettingsComponent} from "./general/general-settings.component";

export const SettingsRoutes: Route = {
    path: 'settings',
    component: SettingsComponent,
    resolve: {settings: SettingsResolve},
    data: {permissions: ['settings.view']},
    children: [
        {path: '', redirectTo: 'providers'},
        {path: 'authentication', component: AuthenticationSettingsComponent},
        {path: 'general', component: GeneralSettingsComponent},
        {path: 'cache', component: CacheSettingsComponent},
        {path: 'permissions', component: PermissionsSettingsComponent},
        {path: 'analytics', component: AnalyticsSettingsComponent},
        {path: 'localization', component: LocalizationSettingsComponent,  resolve: {localizations: LocalizationsResolve}},
        {path: 'mail', component: MailSettingsComponent},
        {path: 'logging', component: LoggingSettingsComponent},
        {path: 'queue', component: QueueSettingsComponent},
        {path: 'providers', component: ProvidersSettingsComponent},
        {path: 'player', component: PlayerSettingsComponent},
        {path: 'genres', component: GenresSettingsComponent},
        {path: 'blocked-artists', component: BlockedArtistsSettingsComponent},
    ]
};

// @NgModule({
//     imports: [RouterModule.forChild(routes)],
//     exports: [RouterModule]
// })
// export class SettingsRoutingModule {}