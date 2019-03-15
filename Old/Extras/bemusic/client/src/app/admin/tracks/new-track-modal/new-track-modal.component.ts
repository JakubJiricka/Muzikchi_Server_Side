import {Component, ElementRef, Renderer2, ViewEncapsulation} from '@angular/core';
import {Settings} from "../../../shared/settings.service";
import {BaseModalClass} from "../../../shared/modal/base-modal";
import {Track} from "../../../shared/types/models/Track";
import {Album} from "../../../shared/types/models/Album";
import {Tracks} from "../../../web-player/tracks/tracks.service";
import {Artist} from "../../../shared/types/models/Artist";
import {UploadFileModalComponent} from "../../../shared/upload-file-modal/upload-file-modal.component";
import {ModalService} from "../../../shared/modal/modal.service";

@Component({
    selector: 'new-track-modal',
    templateUrl: './new-track-modal.component.html',
    styleUrls: ['./new-track-modal.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class NewTrackModalComponent extends BaseModalClass {

    /**
     * Backend validation errors from last create or update request.
     */
    public errors: any = {};

    /**
     * Album this track should be attached to.
     */
    public album: Album;

    /**
     * Whether we are updating or creating a track.
     */
    public updating: boolean = false;

    /**
     * New track model.
     */
    public track = new Track();

    /**
     * Model for artists input and tags.
     */
    public artistsInput = {
        model: '',
        artists: []
    };

    /**
     * NewTrackModalComponent Constructor.
     */
    constructor(
        protected el: ElementRef,
        protected renderer: Renderer2,
        protected settings: Settings,
        protected tracks: Tracks,
        protected modal: ModalService,
    ) {
        super(el, renderer);
    }

    /**
     * Show the modal.
     */
    public show(params: {track?: Track, album: Album, artist?: Artist}) {
        this.hydrate(params);
        super.show(params);
    }

    /**
     * Confirm track creation.
     */
    public confirm() {
        //editing existing track
        if (this.track.id) {
            this.update();
        }

        //creating new track for existing album
        else if (this.album.id) {
            this.create();
        }

        //creating or updating track for new album
        else {
            super.done(this.getPayload());
        }
    }

    /**
     * Update existing track.
     */
    public update() {
        this.tracks.update(this.track.id, this.getPayload()).subscribe(track => {
            this.loading = false;
            super.done(track);
        }, errors => {
            this.loading = false;
            this.errors = errors.messages;
        });
    }

    /**
     * Create a new track.
     */
    public create() {
        this.tracks.create(this.getPayload()).subscribe(track => {
            this.loading = false;
            super.done(track);
        }, errors => {
            this.loading = false;
            this.errors = errors.messages;
        });
    }

    /**
     * Attach a new artist to track.
     */
    public attachArtist() {
        let name = this.artistsInput.model;

        if ( ! name) return;

        //make sure artist is not already attached to track
        if (this.artistsInput.artists.findIndex(curr => curr === name) > -1) return;

        this.artistsInput.artists.push(name);
        this.artistsInput.model = null;
    }

    /**
     * Detach specified artist from track.
     */
    public detachArtist(artist: string) {
        let i = this.artistsInput.artists.findIndex(curr => curr === artist);
        this.artistsInput.artists.splice(i, 1);
    }

    /**
     * Open modal for uploading track streaming file.
     */
    public openUploadMusicModal() {
        const params = {uri: 'videos/static/upload', httpParams: {type: 'track'}};
        this.modal.show(UploadFileModalComponent, params).onDone.subscribe(url => {
            const audio = document.createElement('audio');

            //get uploaded track duration
            this.renderer.listen(audio, 'canplaythrough', (e) => {
                this.track.duration = Math.ceil(e.currentTarget.duration * 1000);
                audio.remove();
            });

            audio.src = url;
            this.track.url = url;
        });
    }

    /**
     * Get track payload for backend.
     */
    private getPayload() {
        this.attachArtist();
        let payload = Object.assign({}, this.track);
        payload.artists = this.artistsInput.artists.join('*|*') as any;
        return payload;
    }

    /**
     * Hydrate track and album models.
     */
    private hydrate(params: {track?: Track, album: Album, artist?: Artist}) {
        this.album = Object.assign({}, params.album);

        //set track model on the modal
        if (params.track) this.track = Object.assign({}, params.track);

        //transform artists string to array
        if (typeof this.track.artists === 'string') {
            this.track.artists = this.track.artists['split']('*|*');
        }

        //track artists should always be an array
        if ( ! this.track.artists) this.track.artists = [];

        //assign track to specified artist
        if (params.artist && ! this.track.artists.length && params.artist.name) {
            this.track.artists.push(params.artist.name);
        }

        //hydrate artists input model
        this.artistsInput.artists = this.track.artists as any;

        //hydrate album_id and album_name
        if ( ! this.track.album_name) this.track.album_name = this.album.name;
        if ( ! this.track.album_id) this.track.album_id = this.album.id;

        //hydrate track number for new tracks
        if ( ! params.track) {
            const num = this.album.tracks && this.album.tracks.length;
            this.track.number = num ? num+1 : 1;
        }

        this.updating = params.track ? true : false;
    }
}
