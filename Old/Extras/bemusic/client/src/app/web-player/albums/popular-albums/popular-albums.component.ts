import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {Album} from "../../../shared/types/models/Album";
import {FilterablePage} from "../../filterable-page/filterable-page";
import {ActivatedRoute} from "@angular/router";

@Component({
    selector: 'popular-albums',
    templateUrl: './popular-albums.component.html',
    styleUrls: ['./popular-albums.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class PopularAlbumsComponent extends FilterablePage<Album> implements OnInit {

    /**
     * PopularAlbumsComponent Constructor.
     */
    constructor(private route: ActivatedRoute) {
        super(['name', 'artist.name']);
    }

    ngOnInit() {
        this.route.data.subscribe((data: {albums: Album[]}) => {
            this.setItems(data.albums);
        });
    }
}
