import {Injectable} from '@angular/core';
import {NavigationEnd, Router} from "@angular/router";
import {Title} from "@angular/platform-browser";
import {Translations} from "./translations/translations.service";
import {Settings} from "./settings.service";
import {utils} from "./utils";
import {Player} from "../web-player/player/player.service";

@Injectable()
export class TitleService {

    /**
     * Data of currently active route.
     */
    private routeData: object;

    /**
     * TitleService Constructor.
     */
    constructor(
        private router: Router,
        private title: Title,
        private i18n: Translations,
        private settings: Settings,
        private player: Player
    ) {}

    /**
     * Init title service.
     */
    public init() {
        this.bindToRouterEvents();
        this.bindToPlaybackEvents();
    }

    /**
     * Get page title for specified route.
     */
    private getTitle(data): string {
        switch (data.name) {
            case 'popular-albums':
                return this.i18n.t('Popular Albums');
            case 'popular-genres':
                return this.i18n.t('Popular Genres');
            case 'top-50':
                return this.i18n.t('Top 50');
            case 'new-releases':
                return this.i18n.t('New Releases');
            case 'account-settings':
                return this.i18n.t('Account Settings');
            case 'library.tracks':
                return this.i18n.t('Library - Songs');
            case 'library.albums':
                return this.i18n.t('Library - Albums');
            case 'library.artists':
                return this.i18n.t('Library - Artists');
            case 'library.playlists':
                return this.i18n.t('Library - Playlists');
            case 'artist':
                return data.artist.get().name;
            case 'album':
                return data.album.name + ' - ' + data.album.artist.name;
            case 'track':
                return data.track.name + ' - ' + data.track.artists[0];
            case 'playlist':
                return data.playlist.get().name;
            case 'user':
                return data.user.display_name;
            case 'genre':
                return utils.ucFirst(data.genreData.genre.name);
            case 'search':
                return this.i18n.t('Search');
            case 'radio':
                return this.i18n.t('Radio') + ' - ' + data.radio.seed.name;
            default:
                return this.getDefaultTitle();
        }
    }

    /**
     * Get default page title.
     */
    private getDefaultTitle() {
        return this.settings.get('branding.site_name');
    }

    /**
     * Change page title on playback start and stop events.
     */
    private bindToPlaybackEvents() {
        this.player.state.onChange$.subscribe(name => {
            if (name === 'PLAYBACK_STARTED') {
                if ( ! this.player.cued()) return;
                const track = this.player.getCuedTrack();
                this.title.setTitle(track.name+' - '+track.artists[0]);
            } else if (name === 'PLAYBACK_PAUSED') {
                try {
                    this.title.setTitle(this.getTitle(this.routeData));
                } catch(e) {
                    this.title.setTitle(this.getDefaultTitle());
                }
            }
        });
    }

    /**
     * Change page title on route change.
     */
    private bindToRouterEvents() {
        this.router.events
            .filter(e => e instanceof NavigationEnd)
            .map(() => {
                let route = this.router.routerState.root;
                while (route.firstChild) route = route.firstChild;
                return route;
            })
            .filter(route => route.outlet === 'primary')
            .mergeMap(route => route.data)
            .subscribe(data => {
                this.routeData = data;
                if (this.player.isPlaying()) return;
                this.title.setTitle(this.getTitle(data))
            });
    }
}
