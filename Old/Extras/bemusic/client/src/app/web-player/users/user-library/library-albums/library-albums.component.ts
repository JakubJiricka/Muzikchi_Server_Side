import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {ActivatedRoute} from "@angular/router";
import {Album} from "../../../../shared/types/models/Album";
import {FilterablePage} from "../../../filterable-page/filterable-page";
import {WebPlayerUrls} from "../../../web-player-urls.service";

@Component({
    selector: 'library-albums',
    templateUrl: './library-albums.component.html',
    styleUrls: ['./library-albums.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class LibraryAlbumsComponent extends FilterablePage<Album> implements OnInit {

    /**
     * Current order for library albums.
     */
    public order = 'date added';

    /**
     * LibraryAlbumsComponent Constructor.
     */
    constructor(private route: ActivatedRoute, public urls: WebPlayerUrls) {
        super(['name', 'artist.name']);
    }

    ngOnInit() {
        this.route.data.subscribe(data => {
            this.setItems(data.albums);
        });
    }
}
