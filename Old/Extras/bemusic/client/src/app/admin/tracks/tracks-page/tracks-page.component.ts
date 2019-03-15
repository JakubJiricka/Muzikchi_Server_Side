import {Component, OnDestroy, OnInit, ViewEncapsulation} from '@angular/core';
import {DataTable} from "../../data-table";
import {Track} from "../../../shared/types/models/Track";
import {ModalService} from "../../../shared/modal/modal.service";
import {ConfirmModalComponent} from "../../../shared/modal/confirm-modal/confirm-modal.component";
import {Tracks} from "../../../web-player/tracks/tracks.service";
import {UrlAwarePaginator} from "../../pagination/url-aware-paginator.service";
import {NewTrackModalComponent} from "../new-track-modal/new-track-modal.component";
import {FormattedDuration} from "../../../web-player/player/formatted-duration.service";
import {CrupdateLyricModalComponent} from "../../lyrics-page/crupdate-lyric-modal/crupdate-lyric-modal.component";
import {ActivatedRoute, Router} from "@angular/router";

@Component({
    selector: 'tracks-page',
    templateUrl: './tracks-page.component.html',
    styleUrls: ['./tracks-page.component.scss'],
    providers: [UrlAwarePaginator],
    encapsulation: ViewEncapsulation.None
})
export class TracksPageComponent extends DataTable implements OnInit, OnDestroy {

    /**
     * TracksPageComponent Constructor.
     */
    constructor(
        public paginator: UrlAwarePaginator,
        private modal: ModalService,
        private tracks: Tracks,
        private duration: FormattedDuration,
        private route: ActivatedRoute,
        private router: Router
    ) {
        super();
    }

    ngOnInit() {
        const params = {order_by: 'spotify_popularity', 'with': 'album,lyric'};
        this.paginator.paginate('tracks', params).subscribe(response => {
            this.items = this.formatTrackDuration(response.data);
        });

        this.crupdateTrackBasedOnQueryParams();
    }

    ngOnDestroy() {
        this.paginator.destroy();
    }

    /**
     * Open modal for editing existing track or creating a new one.
     */
    public openCrupdateTrackModal(track?: Track) {
        this.deselectAllItems();

        this.modal.show(NewTrackModalComponent, {track}).onDone.subscribe(() => {
            this.deselectAllItems();
            this.paginator.refresh();
        });
    }

    /**
     * Open modal for editing or creating track's lyric.
     */
    public openCrupdateLyricModal(track: Track) {
        this.modal.show(CrupdateLyricModalComponent, {track, lyric: track.lyric}).onDone.subscribe(lyric => {
            track.lyric = lyric;
        });
    }

    /**
     * Ask user to confirm deletion of selected tracks
     * and delete selected artists if user confirms.
     */
    public maybeDeleteSelectedTracks() {
        this.modal.show(ConfirmModalComponent, {
            title: 'Delete Tracks',
            body:  'Are you sure you want to delete selected tracks?',
            ok:    'Delete'
        }).onDone.subscribe(() => this.deleteSelectedTracks());
    }

    /**
     * Delete currently selected artists.
     */
    private deleteSelectedTracks() {
        this.tracks.delete(this.selectedItems).subscribe(() => {
            this.deselectAllItems();
            this.paginator.refresh();
        });
    }

    /**
     * Format duration of specified tracks.
     */
    private formatTrackDuration(tracks: Track[]) {
        return tracks.map(track => {
            track['formatted_duration'] = this.duration.fromMilliseconds(track.duration);
            return track;
        });
    }

    /**
     * Open crupdate track modal if track id is specified in query params.
     */
    private crupdateTrackBasedOnQueryParams() {
        let trackId = +this.route.snapshot.queryParamMap.get('track_id'),
            newATrack = this.route.snapshot.queryParamMap.get('newTrack');

        if ( ! trackId && ! newATrack) return;

        this.router.navigate([], {replaceUrl: true}).then(async () => {
            let track = trackId ? await this.tracks.get(trackId).toPromise() : null;

            this.openCrupdateTrackModal(track);
        });
    }
}
