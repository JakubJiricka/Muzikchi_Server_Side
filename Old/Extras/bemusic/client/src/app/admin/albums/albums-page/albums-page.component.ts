import {Component, OnDestroy, OnInit, ViewEncapsulation} from '@angular/core';
import {DataTable} from "../../data-table";
import {Album} from "../../../shared/types/models/Album";
import {Settings} from "../../../shared/settings.service";
import {ModalService} from "../../../shared/modal/modal.service";
import {ConfirmModalComponent} from "../../../shared/modal/confirm-modal/confirm-modal.component";
import {Albums} from "../../../web-player/albums/albums.service";
import {UrlAwarePaginator} from "../../pagination/url-aware-paginator.service";
import {CrupdateAlbumModalComponent} from "../crupdate-album-modal/crupdate-album-modal.component";
import {ActivatedRoute, Router} from "@angular/router";

@Component({
    selector: 'albums-page',
    templateUrl: './albums-page.component.html',
    styleUrls: ['./albums-page.component.scss'],
    providers: [UrlAwarePaginator],
    encapsulation: ViewEncapsulation.None
})
export class AlbumsPageComponent extends DataTable implements OnInit, OnDestroy {

    /**
     * AlbumsPageComponent Constructor.
     */
    constructor(
        public paginator: UrlAwarePaginator,
        private settings: Settings,
        private modal: ModalService,
        private albums: Albums,
        private route: ActivatedRoute,
        private router: Router
    ) {
        super();
    }

    ngOnInit() {
        const params = {order_by: 'spotify_popularity', 'with': 'tracks'};
        this.paginator.paginate('albums', params).subscribe(response => {
            this.items = response.data;
        });

        this.crupdateAlbumBasedOnQueryParams();
    }

    ngOnDestroy() {
        this.paginator.destroy();
    }

    /**
     * Open modal for editing existing album or creating a new one.
     */
    public openCrupdateAlbumModal(album?: Album) {
        this.deselectAllItems();
        const artist = album ? album.artist : null;

        this.modal.show(CrupdateAlbumModalComponent, {album, artist}).onDone.subscribe(() => {
            this.deselectAllItems();
            this.paginator.refresh();
        });
    }

    /**
     * Ask user to confirm deletion of selected albums
     * and delete selected artists if user confirms.
     */
    public maybeDeleteSelectedAlbums() {
        this.modal.show(ConfirmModalComponent, {
            title: 'Delete Albums',
            body:  'Are you sure you want to delete selected albums?',
            ok:    'Delete'
        }).onDone.subscribe(() => this.deleteSelectedAlbums());
    }

    /**
     * Get available album image url or default one.
     */
    public getAlbumImage(album: Album): string {
        if (album.image) return album.image;
        return this.settings.getBaseUrl() + 'assets/images/default/album.png';
    }

    /**
     * Delete currently selected artists.
     */
    private deleteSelectedAlbums() {
        this.albums.delete(this.selectedItems).subscribe(() => {
            this.deselectAllItems();
            this.paginator.refresh();
        });
    }

    /**
     * Open crupdate album modal if album id is specified in query params.
     */
    private crupdateAlbumBasedOnQueryParams() {
        let albumId = +this.route.snapshot.queryParamMap.get('album_id'),
            newAlbum = this.route.snapshot.queryParamMap.get('newAlbum');

        if ( ! albumId && ! newAlbum) return;

        this.router.navigate([], {replaceUrl: true}).then(async () => {
            let album = albumId ? await this.albums.get(albumId).toPromise() : null;

            this.openCrupdateAlbumModal(album);
        });
    }
}
