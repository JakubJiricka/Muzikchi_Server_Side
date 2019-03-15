import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {Album} from "../../../shared/types/models/Album";
import {FilterablePage} from "../../filterable-page/filterable-page";
import {ActivatedRoute} from "@angular/router";

@Component({
    selector: 'new-releases-page',
    templateUrl: './new-releases-page.component.html',
    styleUrls: ['./new-releases-page.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class NewReleasesPageComponent extends FilterablePage<Album> implements OnInit {

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
