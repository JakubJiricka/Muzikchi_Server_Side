import {Component, ElementRef, Renderer2, ViewEncapsulation} from '@angular/core';
import {BaseModalClass} from "../../../shared/modal/base-modal";
import {Album} from "../../../shared/types/models/Album";
import {Settings} from "../../../shared/settings.service";
import {ModalService} from "../../../shared/modal/modal.service";
import {Albums} from "../../../web-player/albums/albums.service";
import {ToastService} from "../../../shared/toast/toast.service";
import {Artist} from "../../../shared/types/models/Artist";
import {UploadFileModalComponent} from "../../../shared/upload-file-modal/upload-file-modal.component";

@Component({
    selector: 'new-album-modal',
    templateUrl: './crupdate-album-modal.component.html',
    styleUrls: ['./crupdate-album-modal.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class CrupdateAlbumModalComponent extends BaseModalClass {

    /**
     * Backend validation errors from last request.
     */
    public errors: any = {};

    /**
     * Whether album is being created or updated currently.
     */
    public loading = false;

    /**
     * Whether we're creating a new album or updating existing one.
     */
    public updating: boolean;

    /**
     * Album model.
     */
    public album = new Album({tracks: []});

    /**
     * Artist new album should be attached to.
     */
    public artist: Artist;

    /**
     * CrupdateAlbumModalComponent Constructor.
     */
    constructor(
        protected el: ElementRef,
        protected renderer: Renderer2,
        protected settings: Settings,
        protected modal: ModalService,
        protected albums: Albums,
        protected toast: ToastService,
    ) {
        super(el, renderer);
    }

    /**
     * Show the modal.
     */
    public show(params: {album?: Album, artist?: Artist}) {
        if (params.album) this.album = Object.assign({}, params.album);
        if (params.artist) this.artist = Object.assign({}, params.artist);
        this.updating = params.album ? true : false;
        super.show(params);
    }

    /**
     * Create or update album and close the modal.
     */
    public async confirm() {
        let album;

        if (this.album.id) {
            album = await this.update();
        } else if (this.artist && this.artist.id) {
            album = await this.create();
        } else {
            super.done(this.album);
        }

        if (album) super.done(this.album);
    }

    /**
     * Create a new album.
     */
    public create(): Promise<Album> {
        let payload = Object.assign({}, this.album);
        payload.artist_id = this.artist.id;

        this.loading = true;

        return this.albums.create(payload).toPromise().then(album => {
            this.loading = false;
            return album;
        }).catch(errors => {
            this.errors = errors.messages;
            this.loading = false;
        }) as any;
    }

    /**
     * Update existing album.
     */
    public update(): Promise<Album> {
        this.loading = true;

        return this.albums.update(this.album.id, Object.assign({}, this.album)).toPromise().then(album => {
            this.loading = false;
            return album;
        }).catch(errors => {
            this.errors = errors.messages;
            this.loading = false;
        }) as any;
    }

    /**
     * Open modal for uploading album image.
     */
    public openInsertImageModal() {
        const params = {uri: 'images/static/upload', httpParams: {type: 'album'}};
        this.modal.show(UploadFileModalComponent, params).onDone.subscribe(url => {
            this.album.image = url;
        });
    }

    /**
     * Get available album image url or default one.
     */
    public getAlbumImage(): string {
        if (this.album.image) return this.album.image;
        return this.settings.getBaseUrl() + 'assets/images/default/album.png';
    }
}
