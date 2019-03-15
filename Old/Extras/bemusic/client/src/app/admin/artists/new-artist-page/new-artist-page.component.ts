import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {Artist} from "../../../shared/types/models/Artist";
import {Settings} from "../../../shared/settings.service";
import {ModalService} from "../../../shared/modal/modal.service";
import {Artists} from "../../../web-player/artists/artists.service";
import {ActivatedRoute, Router} from "@angular/router";
import {ToastService} from "../../../shared/toast/toast.service";
import {UploadFileModalComponent} from "../../../shared/upload-file-modal/upload-file-modal.component";
import {Genre} from "../../../shared/types/models/Genre";

@Component({
    selector: 'new-artist-page',
    templateUrl: './new-artist-page.component.html',
    styleUrls: ['./new-artist-page.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class NewArtistPageComponent implements OnInit {

    /**
     * Backend validation errors from last request.
     */
    public errors: any = {};

    /**
     * New artist model.
     */
    public artist = new Artist({albums: [], genres: []});

    /**
     * Artist biography model.
     */
    public bio = {text: '', images: ''};

    /**
     * Genre input model.
     */
    public genre: string;

    /**
     * NewArtistPageComponent Constructor.
     */
    constructor(
        private settings: Settings,
        private modal: ModalService,
        private artists: Artists,
        private route: ActivatedRoute,
        private toast: ToastService,
        private router: Router,
    ) {}

    ngOnInit() {
        this.bindToRouteData();
    }

    /**
     * Create or update artist based on model id.
     */
    public createOrUpdate() {
        this.artist.id ? this.update() : this.create();
    }

    /**
     * Create a new artist.
     */
    public create() {
        return this.artists.create(this.getPayload()).subscribe(artist => {
            this.artist = artist;
            this.toast.show('Artist created.');
            this.errors = {};
            this.router.navigate(['/admin/artists', this.artist.id, 'edit'], {replaceUrl: true})
        }, errors => {
            this.errors = errors.messages;
        });
    }

    /**
     * Update existing artist.
     */
    public update() {
        return this.artists.update(this.artist.id, this.getPayload()).subscribe(artist => {
            this.artist = artist;
            this.toast.show('Artist updated.');
            this.errors = {};
        }, errors => {
            this.errors = errors.messages;
        });
    }

    /**
     * Add a new genre to artist.
     */
    public addGenre() {
        if ( ! this.genre) return;

        //check if artist already has this genre
        if (this.artist.genres.findIndex(curr => curr.name === this.genre) > -1) return;

        this.artist.genres.push(new Genre({name: this.genre}));
        this.genre = null;
    }

    /**
     * Remove existing genre from artist.
     */
    public removeGenre(genre: Genre) {
        let i = this.artist.genres.findIndex(curr => curr.name === genre.name);
        this.artist.genres.splice(i, 1);
    }

    /**
     * Open modal for uploading a new image for artist.
     */
    public openInsertImageModal(type: 'small'|'large') {
        const params = {uri: 'images/static/upload', httpParams: {type: 'artist'}};
        this.modal.show(UploadFileModalComponent, params).onDone.subscribe(url => {
            this.artist['image_'+type] = url;
        });
    }

    /**
     * Get available artist image url or default one.
     */
    public getArtistImage(): string {
        if (this.artist.image_small) return this.artist.image_small;
        if (this.artist.image_large) return this.artist.image_large;
        return this.settings.getBaseUrl() + 'assets/images/default/artist_small.jpg';
    }

    /**
     * Get payload for creating new artist or updating existing one.
     */
    private getPayload() {
        let payload = Object.assign({}, this.artist);
        let images = this.bio.images.split("\n").map(url => { return {url}});
        payload.bio = JSON.stringify({bio: this.bio.text, images});
        if (this.artist.id) delete payload.albums;
        return payload;
    }

    /**
     * Bind to route data and hydrate artist model.
     */
    private bindToRouteData() {
        this.route.data.subscribe(data => {
            if (data.artist) {
                this.artist = data.artist;
                this.bio.text = data.artist.bio.bio;

                if ( ! data.artist.bio.images) data.artist.bio.images = [];
                this.bio.images = data.artist.bio.images.map(image => {
                    return image.url;
                }).join("\n");
            }
        });
    }
}
