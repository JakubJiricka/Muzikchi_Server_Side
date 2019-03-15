import {NgModule} from '@angular/core';
import {NavSidebarComponent} from './nav-sidebar/nav-sidebar.component';
import {WebPlayerComponent} from "./web-player.component";
import {SearchSlideoutPanelComponent} from './search/search-slideout-panel/search-slideout-panel.component';
import {SearchSlideoutPanel} from "./search/search-slideout-panel/search-slideout-panel.service";
import {SharedModule} from "../shared/shared.module";
import {Search} from "./search/search.service";
import { PopularAlbumsComponent } from './albums/popular-albums/popular-albums.component';
import {WebPlayerRoutingModule} from "./web-player-routing.module";
import {Albums} from "./albums/albums.service";
import {WebPlayerUrls} from "./web-player-urls.service";
import {PopularGenresComponent} from "./genres/popular-genres/popular-genres.component";
import {Genres} from "./genres/genres.service";
import { FilterablePageHeaderComponent } from './filterable-page/filterable-page-header/filterable-page-header.component';
import {Player} from "./player/player.service";
import { PlayerControlsComponent } from './player/player-controls/player-controls.component';
import {PlayerQueue} from "./player/player-queue.service";
import { AlbumComponent } from './albums/album/album.component';
import {AlbumResolver} from "./albums/album/album-resolver.service";
import { TrackListComponent } from './tracks/track-list/track-list.component';
import { PlayingIndicatorComponent } from './tracks/track-list/playing-indicator/playing-indicator.component';
import {YoutubeStrategy} from "./player/strategies/youtube-strategy.service";
import {PopularAlbumsResolver} from "./albums/popular-albums/popular-albums-resolver.service";
import {PopularGenresResolver} from "./genres/popular-genres/popular-genres-resolver.service";
import {PlayerState} from "./player/player-state.service";
import {FormattedDuration} from "./player/formatted-duration.service";
import { QueueSidebarComponent } from './queue-sidebar/queue-sidebar.component';
import { CustomScrollbarDirective } from './custom-scrollbar.directive';
import { VolumeControlsComponent } from './player/player-controls/volume-controls/volume-controls.component';
import { PlayerSeekbarComponent } from './player/player-controls/player-seekbar/player-seekbar.component';
import { PlaybackControlButtonComponent } from './player/playback-control-button/playback-control-button.component';
import { FullscreenOverlayComponent } from './fullscreen-overlay/fullscreen-overlay.component';
import {MainPlaybackButtonsComponent} from './player/player-controls/main-playback-buttons/main-playback-buttons.component';
import {FullscreenOverlay} from "./fullscreen-overlay/fullscreen-overlay.service";
import {RepeatButtonComponent} from './player/player-controls/repeat-button/repeat-button.component';
import {QueueSidebar} from "./queue-sidebar/queue-sidebar.service";
import {UserLibrary} from "./users/user-library/user-library.service";
import { LibraryTrackToggleButtonComponent } from './users/user-library/library-track-toggle-button/library-track-toggle-button.component';
import { LibraryTracksComponent } from './users/user-library/library-tracks/library-tracks.component';
import {LibraryTracksResolver} from "./users/user-library/library-tracks/library-tracks-resolver.service";
import {LibraryTracks} from "./users/user-library/library-tracks.service";
import { LibraryAlbumsComponent } from './users/user-library/library-albums/library-albums.component';
import {LibraryAlbumsResolver} from "./users/user-library/library-albums/library-albums-resolver.service";
import { SortingHeaderComponent } from './filterable-page/sorting-header/sorting-header.component';
import { LibraryArtistsComponent } from './users/user-library/library-artists/library-artists.component';
import {LibraryArtistsResolver} from "./users/user-library/library-artists/library-artists-resolver.service";
import { AlbumItemComponent } from './albums/album-item/album-item.component';
import { MediaGridComponent } from './media-grid/media-grid.component';
import {Artists} from "./artists/artists.service";
import {ArtistItemComponent} from "./artists/artist-item/artist-item.component";
import {ArtistService} from "./artists/artist.service";
import { ArtistComponent } from './artists/artist/artist.component';
import {ArtistResolver} from "./artists/artist/artist-resolver.service";
import { ContextMenuDirective } from './context-menu/context-menu.directive';
import {ContextMenu} from "./context-menu/context-menu.service";
import {AlbumContextMenuComponent} from './albums/album-context-menu/album-context-menu.component';
import { CrupdatePlaylistModalComponent } from './playlists/crupdate-playlist-modal/crupdate-playlist-modal.component';
import { TrackContextMenuComponent } from './tracks/track-context-menu/track-context-menu.component';
import { ArtistsLinksListComponent } from './artists/artists-links-list/artists-links-list.component';
import { ArtistContextMenuComponent } from './artists/artist-context-menu/artist-context-menu.component';
import { GenreComponent } from './genres/genre/genre.component';
import {GenreArtistsResolver} from "./genres/genre/genre-artists-resolver.service";
import {UserPlaylists} from "./playlists/user-playlists.service";
import {Playlists} from "./playlists/playlists.service";
import { ContextMenuPlaylistPanelComponent } from './context-menu/context-menu-playlist-panel/context-menu-playlist-panel.component';
import { PlaylistComponent } from './playlists/playlist/playlist.component';
import {PlaylistResolver} from "./playlists/playlist/playlist-resolver.service";
import { SearchComponent } from './search/search/search.component';
import {SearchResolver} from "./search/search/search-resolver.service";
import {SearchTabValidGuard} from "./search/search/search-tab-valid.guard";
import {PlaylistItemComponent} from "./playlists/playlist-item/playlist-item.component";
import {PlaylistService} from "./playlists/playlist.service";
import { UserItemComponent } from './users/user-item/user-item.component';
import { PlaylistTrackContextMenuComponent } from './playlists/playlist-track-context-menu/playlist-track-context-menu.component';
import { PlaylistContextMenuComponent } from './playlists/playlist-context-menu/playlist-context-menu.component';
import {WebPlayerState} from "./web-player-state.service";
import { TrackPageComponent } from './tracks/track-page/track-page.component';
import {TrackPageResolver} from "./tracks/track-page/track-page-resolver.service";
import {Tracks} from "./tracks/tracks.service";
import { UserProfilePageComponent } from './users/user-profile-page/user-profile-page.component';
import {Users} from "./users/users.service";
import {UserProfilePageResolver} from "./users/user-profile-page/user-profile-page-resolver.service";
import {NewReleasesPageResolver} from "./albums/new-releases-page/new-releases-page.resolver.service";
import {NewReleasesPageComponent} from "./albums/new-releases-page/new-releases-page.component";
import { TopTracksPageComponent } from './tracks/top-tracks-page/top-tracks-page.component';
import {TopTracksPageResolver} from "./tracks/top-tracks-page/top-tracks-page-resolver.service";
import {RadioPageComponent} from './radio-page/radio-page.component';
import {RadioPageResolver} from "./radio-page/radio-page-resolver.service";
import {ShareMediaItemModalComponent} from "./context-menu/share-media-item-modal/share-media-item-modal.component";
import {LyricsModalComponent} from "./lyrics/lyrics-modal/lyrics-modal.component";
import {Lyrics} from "./lyrics/lyrics.service";
import {AccountSettingsComponent} from "./users/account-settings/account-settings.component";
import {ConnectSocialAccountsPanelComponent} from "./users/account-settings/connect-social-accounts-panel/connect-social-accounts-panel.component";
import {AccountSettingsResolve} from "./users/account-settings/account-settings-resolve.service";
import { MobilePlayerControlsComponent } from './player/mobile-player-controls/mobile-player-controls.component';
import { UserLibraryComponent } from './users/user-library/user-library.component';
import { LibraryPlaylistsComponent } from './users/user-library/library-playlists/library-playlists.component';
import {LibraryPlaylistsResolver} from "./users/user-library/library-playlists/library-playlists-resolver.service";
import {Html5Strategy} from "./player/strategies/html5-strategy.service";
import {SoundcloudStrategy} from "./player/strategies/soundcloud-strategy.service";
import { AdHostComponent } from './ad-host/ad-host.component';
import {TrackPlays} from "./player/track-plays.service";

@NgModule({
    imports: [
        SharedModule,
        WebPlayerRoutingModule,
    ],
    declarations: [
        WebPlayerComponent,
        NavSidebarComponent,
        PopularAlbumsComponent,
        NewReleasesPageComponent,
        PopularGenresComponent,
        SearchSlideoutPanelComponent,
        FilterablePageHeaderComponent,
        PlayerControlsComponent,
        AlbumComponent,
        TrackListComponent,
        PlayingIndicatorComponent,
        QueueSidebarComponent,
        CustomScrollbarDirective,
        VolumeControlsComponent,
        PlayerSeekbarComponent,
        PlaybackControlButtonComponent,
        FullscreenOverlayComponent,
        MainPlaybackButtonsComponent,
        RepeatButtonComponent,
        LibraryTrackToggleButtonComponent,
        LibraryTracksComponent,
        LibraryAlbumsComponent,
        SortingHeaderComponent,
        LibraryArtistsComponent,
        AlbumItemComponent,
        ArtistItemComponent,
        UserItemComponent,
        PlaylistItemComponent,
        MediaGridComponent,
        ArtistComponent,
        ContextMenuDirective,
        AlbumContextMenuComponent,
        CrupdatePlaylistModalComponent,
        TrackContextMenuComponent,
        ArtistsLinksListComponent,
        ArtistContextMenuComponent,
        GenreComponent,
        ContextMenuPlaylistPanelComponent,
        PlaylistComponent,
        SearchComponent,
        UserItemComponent,
        PlaylistTrackContextMenuComponent,
        PlaylistContextMenuComponent,
        TrackPageComponent,
        UserProfilePageComponent,
        TopTracksPageComponent,
        RadioPageComponent,
        ShareMediaItemModalComponent,
        LyricsModalComponent,
        AccountSettingsComponent,
        ConnectSocialAccountsPanelComponent,
        MobilePlayerControlsComponent,
        UserLibraryComponent,
        LibraryPlaylistsComponent,
        AdHostComponent
    ],
    entryComponents: [
        AlbumContextMenuComponent,
        TrackContextMenuComponent,
        ArtistContextMenuComponent,
        PlaylistContextMenuComponent,
        PlaylistTrackContextMenuComponent,
        CrupdatePlaylistModalComponent,
        ShareMediaItemModalComponent,
        LyricsModalComponent,
    ],
    providers: [
        Albums,
        Artists,
        Genres,
        Search,
        Player,
        ArtistService,
        PlaylistService,
        YoutubeStrategy,
        Html5Strategy,
        SoundcloudStrategy,
        WebPlayerUrls,
        PlayerQueue,
        PlayerState,
        FormattedDuration,
        QueueSidebar,
        SearchSlideoutPanel,
        FullscreenOverlay,
        AlbumResolver,
        PopularAlbumsResolver,
        PopularGenresResolver,
        GenreArtistsResolver,
        PlaylistResolver,
        ArtistResolver,
        NewReleasesPageResolver,
        UserLibrary,
        SearchResolver,
        LibraryTracksResolver,
        LibraryAlbumsResolver,
        LibraryArtistsResolver,
        LibraryPlaylistsResolver,
        LibraryTracks,
        UserPlaylists,
        Playlists,
        ContextMenu,
        SearchTabValidGuard,
        WebPlayerState,
        TrackPageResolver,
        Tracks,
        Users,
        UserProfilePageResolver,
        TopTracksPageResolver,
        RadioPageResolver,
        Lyrics,
        AccountSettingsResolve,
        TrackPlays
    ]
})
export class WebPlayerModule {
}
