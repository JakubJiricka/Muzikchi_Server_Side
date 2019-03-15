import {Component, OnDestroy, OnInit, ViewEncapsulation} from '@angular/core';
import {ModalService} from "../../shared/modal/modal.service";
import {ConfirmModalComponent} from "../../shared/modal/confirm-modal/confirm-modal.component";
import {CurrentUser} from "../../auth/current-user";
import {UrlAwarePaginator} from "../pagination/url-aware-paginator.service";
import {DataTable} from "../data-table";
import {Artists} from "../../web-player/artists/artists.service";

@Component({
    selector: 'artists',
    templateUrl: './artists.component.html',
    providers: [UrlAwarePaginator],
    encapsulation: ViewEncapsulation.None,
})
export class ArtistsComponent extends DataTable implements OnInit, OnDestroy {

    /**
     * UsersComponent Constructor.
     */
    constructor(
        public paginator: UrlAwarePaginator,
        private artists: Artists,
        private modal: ModalService,
        public currentUser: CurrentUser,
    ) {
        super();
    }

    ngOnInit() {
        this.paginator.paginate('artists', {order_by: 'spotify_popularity'}).subscribe(response => {
            this.items = response.data;
        });
    }

    ngOnDestroy() {
        this.paginator.destroy();
    }

    /**
     * Ask user to confirm deletion of selected artists
     * and delete selected artists if user confirms.
     */
    public maybeDeleteSelectedArtists() {
        this.modal.show(ConfirmModalComponent, {
            title: 'Delete Artists',
            body:  'Are you sure you want to delete selected artists?',
            ok:    'Delete'
        }).onDone.subscribe(() => this.deleteSelectedArtists());
    }

    /**
     * Delete currently selected artists.
     */
    public deleteSelectedArtists() {
        this.artists.delete(this.selectedItems).subscribe((a) => {
            console.log(a);
            this.paginator.refresh();
            this.selectedItems = [];
        });
    }
}
