import {NgModule} from '@angular/core';
import {AssignUsersToGroupModalComponent} from "./groups/assign-users-to-group-modal/assign-users-to-group-modal.component";
import {CrupdateGroupModalComponent} from "./groups/crupdate-group-modal/crupdate-group-modal.component";
import {GroupService} from "./groups/group.service";
import {UsersComponent} from "./users/users.component";
import {AdminRoutingModule} from "./admin-routing.module";
import {AdminComponent} from "./admin.component";
import {CrupdateUserModalComponent} from "./users/crupdate-user-modal/crupdate-user-modal.component";
import {GroupsComponent} from "./groups/groups.component";
import {ErrorLogComponent} from './error-log/error-log.component';
import {PagesComponent} from './pages/pages.component';
import {CrupdatePageComponent} from './pages/crupdate-page/crupdate-page.component';
import {TranslationsComponent} from './translations/translations.component';
import {CrupdateLocalizationModalComponent} from "./translations/crupdate-localization-modal/crupdate-localization-modal.component";
import {LocalizationsResolve} from "./translations/localizations-resolve.service";
import { MailTemplatesComponent } from './mail-templates/mail-templates.component';
import { MailTemplatePreviewComponent } from './mail-templates/mail-template-preview/mail-template-preview.component';
import {MailTemplatesResolve} from "./mail-templates/mail-templates-resolve.service";
import {SharedModule} from "../shared/shared.module";
import {TextEditorModule} from "../text-editor/text-editor.module";
import {PaginationControlsComponent} from "./pagination/pagination-controls/pagination-controls.component";
import {UserAccessManagerComponent} from "./users/user-access-manager/user-access-manager.component";
import {SelectGroupsModalComponent} from "./users/select-groups-modal/select-groups-modal.component";
import {ArtistsComponent} from "./artists/artists.component";
import { NewArtistPageComponent } from './artists/new-artist-page/new-artist-page.component';
import { CrupdateAlbumModalComponent } from './albums/crupdate-album-modal/crupdate-album-modal.component';
import { NewTrackModalComponent } from './tracks/new-track-modal/new-track-modal.component';
import {EditArtistPageResolver} from "./artists/new-artist-page/edit-artist-page-resolver.service";
import {ModalService} from "../shared/modal/modal.service";
import { AlbumTracksTableComponent } from './albums/crupdate-album-modal/album-tracks-table/album-tracks-table.component';
import {ArtistAlbumsTableComponent} from "./artists/new-artist-page/artist-albums-table/artist-albums-table.component";
import { AlbumsPageComponent } from './albums/albums-page/albums-page.component';
import {TracksPageComponent} from "./tracks/tracks-page/tracks-page.component";
import { LyricsPageComponent } from './lyrics-page/lyrics-page.component';
import { CrupdateLyricModalComponent } from './lyrics-page/crupdate-lyric-modal/crupdate-lyric-modal.component';
import {SelectPermissionsModalComponent} from "./users/select-permissions-modal/select-permissions-modal.component";
import {AppearanceModule} from "./appearance/appearance.module";
import { AdsPageComponent } from './ads-page/ads-page.component';
import {PlaylistsPageComponent} from "./playlists-page/playlists-page.component";
import {SettingsModule} from "./settings/settings.module";

@NgModule({
    imports:      [
        SharedModule,
        AdminRoutingModule,
        TextEditorModule,
        AppearanceModule,
        SettingsModule,
    ],
    declarations: [
        AdminComponent,
        ErrorLogComponent,
        GroupsComponent,
        CrupdateGroupModalComponent,
        AssignUsersToGroupModalComponent,
        UsersComponent,
        CrupdateUserModalComponent,
        TranslationsComponent,
        CrupdateLocalizationModalComponent,
        PagesComponent,
        CrupdatePageComponent,
        MailTemplatesComponent,
        MailTemplatePreviewComponent,
        PaginationControlsComponent,
        UserAccessManagerComponent,
        SelectGroupsModalComponent,
        ArtistsComponent,
        NewArtistPageComponent,
        CrupdateAlbumModalComponent,
        NewTrackModalComponent,
        AlbumTracksTableComponent,
        ArtistAlbumsTableComponent,
        AlbumsPageComponent,
        TracksPageComponent,
        SelectPermissionsModalComponent,
        LyricsPageComponent,
        PlaylistsPageComponent,
        CrupdateLyricModalComponent,
        AdsPageComponent,
    ],
    entryComponents: [
        CrupdateUserModalComponent,
        CrupdateGroupModalComponent,
        AssignUsersToGroupModalComponent,
        CrupdateLocalizationModalComponent,
        SelectGroupsModalComponent,
        CrupdateAlbumModalComponent,
        NewTrackModalComponent,
        CrupdateLyricModalComponent,
        SelectPermissionsModalComponent,
    ],
    exports:      [],
    providers:    [
        GroupService,
        LocalizationsResolve,
        MailTemplatesResolve,
        ModalService,
        EditArtistPageResolver,
    ]
})
export class AdminModule { }