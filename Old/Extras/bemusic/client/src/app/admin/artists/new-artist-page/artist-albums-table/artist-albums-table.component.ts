import {Component, Input, OnInit, ViewEncapsulation} from '@angular/core';
import {FormControl} from "@angular/forms";
import {ModalService} from "../../../../shared/modal/modal.service";
import {Album} from "../../../../shared/types/models/Album";
import {Artist} from "../../../../shared/types/models/Artist";
import {CrupdateAlbumModalComponent} from "../../../albums/crupdate-album-modal/crupdate-album-modal.component";
import {ConfirmModalComponent} from "../../../../shared/modal/confirm-modal/confirm-modal.component";
import {Albums} from "../../../../web-player/albums/albums.service";
import {Settings} from "../../../../shared/settings.service";
import {utils} from "../../../../shared/utils";

@Component({
    selector: 'artist-albums-table',
    templateUrl: './artist-albums-table.component.html',
    styleUrls: ['./artist-albums-table.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class ArtistAlbumsTableComponent implements OnInit {

    /**
     * Form control for DataTable search input.
     */
    public searchQuery = new FormControl();

    /**
     * Whether all tracks are currently selected.
     */
    public allAlbumsSelected: boolean = false;

    /**
     * All currently selected albums.
     */
    public selectedAlbums = [];

    /**
     * Albums filtered by search query.
     */
    public filteredAlbums: Album[] = [];

    /**
     * Artist albums belong to.
     */
    @Input() artist: Artist;

    /**
     * ArtistAlbumsTableComponent Constructor.
     */
    constructor(
        private modal: ModalService,
        private albums: Albums,
        private settings: Settings
    ) {}

    ngOnInit() {
        this.bindSearchQuery();
        this.filteredAlbums = this.artist.albums.slice();
    }

    /**
     * Confirm and delete selected albums.
     */
    public maybeDeleteSelectedAlbums() {
        this.modal.show(ConfirmModalComponent, {
            title: 'Delete Albums',
            body: 'Are you sure you want to delete selected albums?',
            ok: 'Delete'
        }).onDone.subscribe(async () => {
            let ids = this.selectedAlbums.filter(identifier => {
                return this.filteredAlbums.find(curr => this.getIdentifier(curr) === identifier).id;
            });

            console.log(ids);

            if (ids.length) {
                await this.albums.delete(ids).toPromise();
            }

            this.selectedAlbums.forEach(identifier => {
                let i = this.filteredAlbums.findIndex(curr => this.getIdentifier(curr) === identifier);
                let k = this.artist.albums.findIndex(curr => this.getIdentifier(curr) === identifier);
                this.filteredAlbums.splice(i, 1);
                this.artist.albums.splice(k, 1);
            });

            this.deselectAllItems();
        });
    }

    /**
     * Open modal for creating a new album.
     */
    public openNewAlbumModal() {
        this.deselectAllItems();

        this.modal.show(CrupdateAlbumModalComponent, {artist: this.artist}).onDone.subscribe(album => {
            album = this.setIdentifier(album);
            this.artist.albums.push(album);
            this.filteredAlbums.push(album);
        });
    }

    /**
     * Open modal for editing existing album.
     */
    public openEditAlbumModal(album: Album) {
        this.deselectAllItems();

        this.modal.show(CrupdateAlbumModalComponent, {artist: this.artist, album}).onDone.subscribe(album => {
            let i = this.filteredAlbums.findIndex(curr => this.getIdentifier(curr) === this.getIdentifier(album));
            let k = this.artist.albums.findIndex(curr => this.getIdentifier(curr) === this.getIdentifier(album));
            album = this.setIdentifier(album);
            this.filteredAlbums[i] = album;
            this.artist.albums[k] = album;
        });
    }

    /**
     * Check if given album is currently selected.
     */
    public isAlbumSelected(album: string) {
        return this.selectedAlbums.indexOf(album) > -1;
    }

    /**
     * Selected or deselect specified album.
     */
    public toggleSelectedAlbum(album: string) {
        let index = this.selectedAlbums.indexOf(album);

        if (index > -1) {
            this.selectedAlbums.splice(index, 1);
        } else {
            this.selectedAlbums.push(album);
        }
    }

    /**
     * Select or de-select all tracks.
     */
    public toggleAllSelectedAlbums() {
        if (this.allAlbumsSelected) {
            this.selectedAlbums = [];
        } else {
            this.selectedAlbums = this.filteredAlbums.map(album => this.getIdentifier(album));
        }

        this.allAlbumsSelected = !this.allAlbumsSelected;
    }

    /**
     * Deselect all currently selected items.
     */
    public deselectAllItems() {
        this.selectedAlbums = [];
        this.allAlbumsSelected = false;
    }

    /**
     * Get available album image url or default one.
     */
    public getAlbumImage(album: Album): string {
        if (album.image) return album.image;
        return this.settings.getBaseUrl() + 'assets/images/default/album.png';
    }

    /**
     * If album is not created in backend yet, assign an identifier
     * by which album can by found in albums array for editing.
     */
    public setIdentifier(album: Album): Album {
        if ( ! album.id) album.temp_id = utils.randomString();
        return album;
    }

    /**
     * Get album identifier.
     */
    public getIdentifier(album: Album) {
        return album.id || album.temp_id;
    }

    /**
     * Bind album list search form control.
     */
    private bindSearchQuery() {
        this.searchQuery.valueChanges
            .debounceTime(150)
            .distinctUntilChanged()
            .subscribe(query => {
                let albums = this.artist.albums.slice();
                this.filteredAlbums = albums.filter(album => {
                    return utils.strContains(album.name, query);
                });
            });
    }
}
