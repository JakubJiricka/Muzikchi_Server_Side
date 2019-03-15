import {Component, ElementRef, Renderer2, ViewEncapsulation} from '@angular/core';
import {BaseModalClass} from "../../../shared/modal/base-modal";
import {Playlist} from "../../../shared/types/models/Playlist";
import {Playlists} from "../playlists.service";
import {Settings} from "../../../shared/settings.service";
import {ModalService} from "../../../shared/modal/modal.service";
import {UploadFileModalComponent} from "../../../shared/upload-file-modal/upload-file-modal.component";
import {Observable} from "rxjs/Observable";

@Component({
    selector: 'crupdate-playlist-modal',
    templateUrl: './crupdate-playlist-modal.component.html',
    styleUrls: ['./crupdate-playlist-modal.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class CrupdatePlaylistModalComponent extends BaseModalClass {

    /**
     * New playlist model.
     */
    public model = new Playlist({'public': 1});

    /**
     * NewPlaylistModal Component.
     */
    constructor(
        protected el: ElementRef,
        protected renderer: Renderer2,
        private playlists: Playlists,
        private settings: Settings,
        private modal: ModalService,
    ) {
        super(el, renderer);
    }

    /**
     * Show the modal.
     */
    public show(params: {playlist?: Playlist}) {
        if (params.playlist) this.model = Object.assign({}, params.playlist);
        this.setDefaultImageUrl();
        super.show(params);
    }

    /**
     * Close modal and emit crupdated playlist.
     */
    public confirm() {
        this.loading = true;

        this.crupdatePlaylist().finally(() => {
            this.loading = false;
        }).subscribe(playlist => {
            super.done(playlist);
        }, this.handleErrors.bind(this));
    }

    /**
     * Create new playlist or update existing one.
     */
    private crupdatePlaylist(): Observable<Playlist> {
        const payload = {
            name: this.model.name,
            image: this.model.image,
            'public': this.model.public,
            description: this.model.description,
        };

        if (this.model.id) {
            return this.playlists.update(this.model.id, payload);
        } else {
            return this.playlists.create(payload);
        }
    }

    /**
     * Open modal for uploading playlist image.
     */
    public openImageUploadModal() {
        const params = {uri: 'images/static/upload', httpParams: {type: 'playlist'}};
        this.modal.show(UploadFileModalComponent, params).onDone.subscribe(url => {
            this.model.image = url;
        });
    }

    /**
     * Set default image on model, if it does not already exist.
     */
    private setDefaultImageUrl() {
        if (this.model.image) return;
        this.model.image = this.settings.getBaseUrl() + 'assets/images/default/artist_small.jpg';
    }
}
