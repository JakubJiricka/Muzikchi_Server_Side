import {Component, Input, OnInit, ViewEncapsulation} from '@angular/core';
import {FormControl} from "@angular/forms";
import {Album} from "../../../../shared/types/models/Album";
import {Track} from "../../../../shared/types/models/Track";
import {NewTrackModalComponent} from "../../../tracks/new-track-modal/new-track-modal.component";
import {ModalService} from "../../../../shared/modal/modal.service";
import {Artist} from "../../../../shared/types/models/Artist";
import {ConfirmModalComponent} from "../../../../shared/modal/confirm-modal/confirm-modal.component";
import {Tracks} from "../../../../web-player/tracks/tracks.service";
import {utils} from "../../../../shared/utils";
import {FormattedDuration} from "../../../../web-player/player/formatted-duration.service";

@Component({
    selector: 'album-tracks-table',
    templateUrl: './album-tracks-table.component.html',
    styleUrls: ['./album-tracks-table.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class AlbumTracksTableComponent implements OnInit {

    /**
     * Form control for DataTable search input.
     */
    public searchQuery = new FormControl();

    /**
     * Whether all tracks are currently selected.
     */
    public allTracksSelected: boolean = false;

    /**
     * All currently selected tracks.
     */
    public selectedTracks = [];

    /**
     * Tracks filtered by search query.
     */
    public filteredTracks: Track[] = [];

    /**
     * Album tracks belong to.
     */
    @Input() album: Album;

    /**
     * Artist album belongs to.
     */
    @Input() artist: Artist;

    /**
     * AlbumTracksTableComponent Constructor.
     */
    constructor(
        private modal: ModalService,
        private tracks: Tracks,
        public duration: FormattedDuration,
    ) {}

    ngOnInit() {
        this.bindSearchQuery();
        this.filteredTracks = this.album.tracks.slice();
    }

    /**
     * Open modal for creating a new track.
     */
    public openNewTrackModal() {
        this.deselectAllItems();

        this.modal.show(NewTrackModalComponent, {album: this.album, artist: this.artist}).onDone.subscribe(track => {
            track = this.setIdentifier(track);
            this.album.tracks.push(track);
            this.filteredTracks.push(track);
        });
    }

    /**
     * Open modal for editing existing track.
     */
    public openEditTrackModal(track: Track) {
        this.deselectAllItems();

        this.modal.show(NewTrackModalComponent, {album: this.album, track, artist: this.artist}).onDone.subscribe(track => {
            let i = this.filteredTracks.findIndex(curr => this.getIdentifier(curr) === this.getIdentifier(track));
            let k = this.album.tracks.findIndex(curr => this.getIdentifier(curr) === this.getIdentifier(track));
            track = this.setIdentifier(track);
            this.filteredTracks[i] = track;
            this.album.tracks[k] = track;
        });
    }

    /**
     * Confirm and delete selected tracks.
     */
    public maybeDeleteSelectedTracks() {
        this.modal.show(ConfirmModalComponent, {
            title: 'Delete Tracks',
            body: 'Are you sure you want to delete selected tracks?',
            ok: 'Delete'
        }).onDone.subscribe(async () => {
            let ids = this.selectedTracks.map(identifier => {
                return this.filteredTracks.find(curr => this.getIdentifier(curr) === identifier).id;
            }).filter(id => id);

            if (ids.length) {
                await this.tracks.delete(ids).toPromise();
            }

            this.selectedTracks.forEach(identifier => {
                let i = this.filteredTracks.findIndex(curr => this.getIdentifier(curr) === identifier);
                let k = this.album.tracks.findIndex(curr => this.getIdentifier(curr) === identifier);
                this.filteredTracks.splice(i, 1);
                this.artist.albums.splice(k, 1);
            });

            this.deselectAllItems();
        });
    }

    /**
     * Check if given track is currently selected.
     */
    public isTrackSelected(track: string) {
        return this.selectedTracks.indexOf(track) > -1;
    }

    /**
     * Selected or deselect specified track.
     */
    public toggleSelectedTrack(track: string) {
        let index = this.selectedTracks.indexOf(track);

        if (index > -1) {
            this.selectedTracks.splice(index, 1);
        } else {
            this.selectedTracks.push(track);
        }
    }

    /**
     * Select or de-select all tracks.
     */
    public toggleAllSelectedTracks() {
        if (this.allTracksSelected) {
            this.selectedTracks = [];
        } else {
            this.selectedTracks = this.filteredTracks.map(track => this.getIdentifier(track));
        }

        this.allTracksSelected = !this.allTracksSelected;
    }

    /**
     * Deselect all currently selected items.
     */
    public deselectAllItems() {
        this.selectedTracks = [];
        this.allTracksSelected = false;
    }

    /**
     * If track is not created in backend yet, assign an identifier
     * by which track can by find in tracks array for editing.
     */
    public setIdentifier(track: Track): Track {
        if ( ! track.id) track.temp_id = utils.randomString();
        return track;
    }

    /**
     * Get track identifier.
     */
    public getIdentifier(track: Track) {
        return track.id || track.temp_id;
    }

    /**
     * Bind track list search form control.
     */
    private bindSearchQuery() {
        this.searchQuery.valueChanges
            .debounceTime(150)
            .distinctUntilChanged()
            .subscribe(query => {
                let tracks = this.album.tracks.slice();
                this.filteredTracks = tracks.filter(track => {
                    return utils.strContains(track.name, query);
                });
            });
    }
}
