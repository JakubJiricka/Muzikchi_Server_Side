import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {ActivatedRoute} from "@angular/router";
import {Track} from "../../shared/types/models/Track";
import {Artist} from "../../shared/types/models/Artist";
import {Player} from "../player/player.service";
import {Translations} from "../../shared/translations/translations.service";

@Component({
    selector: 'radio-page',
    templateUrl: './radio-page.component.html',
    styleUrls: ['./radio-page.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class RadioPageComponent implements OnInit {

    /**
     * Tracks recommended for seed radio.
     */
    public tracks: Track[];

    /**
     * Seed radio was generated for.
     */
    public seed: Artist|Track;

    /**
     * Type of radio seed.
     */
    public type: string;

    /**
     * RadioPageComponent Constructor.
     */
    constructor(
        private route: ActivatedRoute,
        private player: Player,
        private i18n: Translations
    ) {}

    ngOnInit() {
        this.route.data.subscribe(data => {
            this.seed = data.radio.seed;
            this.type = this.i18n.t(data.radio.type);

            this.tracks = data.radio.recommendations.map(track => {
                return new Track(track);
            });
        });
    }

    /**
     * Get image for this radio.
     */
    public getImage(): string {
        return this.seed['image_small'] || this.seed['album'].image;
    }

    /**
     * Check if this radio is playing currently.
     */
    public playing(): boolean {
        return this.player.isPlaying() && this.isQueued();
    }

    /**
     * Play the radio.
     */
    public async play() {
        if ( ! this.isQueued()) {
            await this.player.overrideQueue({tracks: this.tracks, queuedItemId: this.getQueueId()});
        }

        this.player.play();
    }

    /**
     * Pause the player.
     */
    public pause() {
        this.player.pause();
    }

    /**
     * Get queue ID for this radio.
     */
    private getQueueId(): string {
        return 'radio.'+this.seed.id
    }

    /**
     * Check if this radio is currently queued in player.
     */
    private isQueued(): boolean {
        return this.player.queue.itemIsQueued(this.getQueueId());
    }

}
