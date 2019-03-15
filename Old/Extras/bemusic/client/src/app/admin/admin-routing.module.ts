import {NgModule} from "@angular/core";
import {RouterModule, Routes} from '@angular/router';
import {GroupsComponent} from "./groups/groups.component";
import {UsersComponent} from "./users/users.component";
import {AdminComponent} from "./admin.component";
import {AuthGuard} from "../guards/auth-guard.service";
import {PagesComponent} from "./pages/pages.component";
import {CrupdatePageComponent} from "./pages/crupdate-page/crupdate-page.component";
import {TranslationsComponent} from "./translations/translations.component";
import {LocalizationsResolve} from "./translations/localizations-resolve.service";
import {MailTemplatesComponent} from "./mail-templates/mail-templates.component";
import {MailTemplatesResolve} from "./mail-templates/mail-templates-resolve.service";
import {ArtistsComponent} from "./artists/artists.component";
import {NewArtistPageComponent} from "./artists/new-artist-page/new-artist-page.component";
import {EditArtistPageResolver} from "./artists/new-artist-page/edit-artist-page-resolver.service";
import {AlbumsPageComponent} from "./albums/albums-page/albums-page.component";
import {TracksPageComponent} from "./tracks/tracks-page/tracks-page.component";
import {LyricsPageComponent} from "./lyrics-page/lyrics-page.component";
import {AdsPageComponent} from "./ads-page/ads-page.component";
import {PlaylistsPageComponent} from "./playlists-page/playlists-page.component";
import * as SettingsRoutes from "./settings/settings-routing.module";
import {CheckPermissionsGuard} from "../guards/check-permissions-guard.service";

const routes: Routes = [
    {path: '', component: AdminComponent, canActivate: [AuthGuard], canActivateChild: [AuthGuard, CheckPermissionsGuard], children: [
        {
            path: '',
            redirectTo: 'analytics',
            pathMatch: 'full',
        },
        {
            path: 'analytics',
            loadChildren: 'app/admin/analytics/analytics.module#AnalyticsModule'
        },
        SettingsRoutes.SettingsRoutes,
        {
            path: 'users',
            component: UsersComponent,
            data: {permissions: ['users.view']}
        },
        {
            path: 'artists',
            children: [
                {path: '', component: ArtistsComponent, data: {permissions: ['artists.update']}},
                {path: 'new', component: NewArtistPageComponent, data: {permissions: ['artists.create']}},
                {path: ':id/edit', component: NewArtistPageComponent, resolve: {artist: EditArtistPageResolver}, data: {permissions: ['artists.update']}},
            ]
        },
        {
            path: 'albums',
            component: AlbumsPageComponent,
            data: {permissions: ['albums.update']}
        },
        {
            path: 'tracks',
            component: TracksPageComponent,
            data: {permissions: ['tracks.update']}
        },
        {
            path: 'lyrics',
            component: LyricsPageComponent,
            data: {permissions: ['lyrics.update']}
        },
        {
            path: 'playlists',
            component: PlaylistsPageComponent,
            data: {permissions: ['playlists.update']}
        },
        {
            path: 'groups',
            component: GroupsComponent,
            data: {permissions: ['groups.view']}
        },
        {
            path: 'translations',
            component: TranslationsComponent,
            resolve: {localizations: LocalizationsResolve},
            data: {permissions: ['localizations.view']}
        },
        {
            path: 'mail-templates',
            component: MailTemplatesComponent,
            resolve: {templates: MailTemplatesResolve},
            data: {permissions: ['mail_templates.view']}
        },
        {
            path: 'pages',
            component: PagesComponent,
            data: {permissions: ['pages.view']}},
        {
            path: 'pages/new',
            component: CrupdatePageComponent,
            data: {permissions: ['pages.create']}
        },
        {
            path: 'pages/:id/edit',
            component: CrupdatePageComponent,
            data: {permissions: ['pages.update']}
        },
        {
            path: 'ads',
            component: AdsPageComponent,
            data: {permissions: ['ads.update']}
        },
    ]},
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class AdminRoutingModule {}