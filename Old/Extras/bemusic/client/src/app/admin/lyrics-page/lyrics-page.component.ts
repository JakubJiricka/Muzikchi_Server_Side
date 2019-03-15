import {Component, OnDestroy, OnInit, ViewEncapsulation} from '@angular/core';
import {DataTable} from "../data-table";
import {UrlAwarePaginator} from "../pagination/url-aware-paginator.service";
import {Lyrics} from "../../web-player/lyrics/lyrics.service";
import {ModalService} from "../../shared/modal/modal.service";
import {CurrentUser} from "../../auth/current-user";
import {ConfirmModalComponent} from "../../shared/modal/confirm-modal/confirm-modal.component";
import {Lyric} from "../../shared/types/models/Lyric";
import {Settings} from "../../shared/settings.service";
import {CrupdateLyricModalComponent} from "./crupdate-lyric-modal/crupdate-lyric-modal.component";

@Component({
    selector: 'lyrics-page',
    templateUrl: './lyrics-page.component.html',
    styleUrls: ['./lyrics-page.component.scss'],
    providers: [UrlAwarePaginator],
    encapsulation: ViewEncapsulation.None
})
export class LyricsPageComponent extends DataTable implements OnInit, OnDestroy {

    /**
     * PagesComponent Constructor.
     */
    constructor(
        public paginator: UrlAwarePaginator,
        private lyrics: Lyrics,
        private modal: ModalService,
        public currentUser: CurrentUser,
        private settings: Settings
    ) {
        super();
    }

    /**
     * Fetch initial pages to display.
     */
    ngOnInit() {
        this.paginator.paginate('lyrics', {'with': 'track.album.artist'}).subscribe(response => {
            this.items = response.data;
        });
    }

    ngOnDestroy() {
        this.paginator.destroy();
    }

    /**
     * Show modal for creating a new lyric or updating existing one.
     */
    public showCrupdateLyricModal(lyric?: Lyric) {
        this.modal.show(CrupdateLyricModalComponent, {lyric}).onDone.subscribe(() => {
            this.deselectAllItems();
            this.paginator.refresh();
        });
    }

    /**
     * Ask user to confirm deletion of selected lyrics
     * and delete selected lyrics if user confirms.
     */
    public confirmLyricsDeletion() {
        this.modal.show(ConfirmModalComponent, {
            title: 'Delete Lyrics',
            body: 'Are you sure you want to delete selected lyrics?',
            ok: 'Delete'
        }).onDone.subscribe(() => this.deleteSelectedLyrics());
    }

    /**
     * Delete currently selected pages.
     */
    public deleteSelectedLyrics() {
        this.lyrics.delete(this.selectedItems).subscribe(() => {
            this.paginator.refresh();
            this.deselectAllItems();
        });
    }

    /**
     * Get image for specified lyric.
     */
    public getLyricImage(lyric: Lyric): string {
        if (lyric.track.album.image) return lyric.track.album.image;
        return this.settings.getBaseUrl() + 'assets/images/default/album.png';
    }
}
