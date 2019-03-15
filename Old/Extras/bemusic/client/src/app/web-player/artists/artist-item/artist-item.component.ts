import {Component, Input, OnDestroy, ViewEncapsulation} from '@angular/core';
import {WebPlayerUrls} from "../../web-player-urls.service";
import {Player} from "../../player/player.service";
import {Artist} from "../../../shared/types/models/Artist";
import {ArtistService} from "../artist.service";
import {Settings} from "../../../shared/settings.service";

@Component({
    selector: 'artist-item',
    templateUrl: './artist-item.component.html',
    styleUrls: ['./artist-item.component.scss'],
    encapsulation: ViewEncapsulation.None,
    host: {'class': 'media-grid-item', '[class.active]': 'playing()'},
})
export class ArtistItemComponent implements OnDestroy {

    /**
     * Scroll container for lazy loading images.
     */
    @Input() scrollContainer: HTMLElement;

    /**
     * Artist to be displayed.
     */
    @Input() artist: Artist;

    /**
     * ArtistItemComponent Constructor
     */
    constructor(
        public urls: WebPlayerUrls,
        private artistService: ArtistService,
        private player: Player,
        public settings: Settings
    ) {}

    ngOnDestroy() {
        this.artistService.destroy();
    }

    /**
     * Check if artist is playing.
     */
    public playing() {
        return this.artistService.playing(this.artist);
    }

    /**
     * Play all artist tracks.
     */
    public async play() {
        this.player.stop();
        this.player.state.buffering = true;
        await this.artistService.init(this.artist.id, {top_tracks: true});
        this.artistService.play();
    }

    /**
     * Pause the player.
     */
    public pause() {
        this.player.pause();
    }
}
