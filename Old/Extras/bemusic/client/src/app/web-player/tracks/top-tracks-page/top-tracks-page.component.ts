import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {FilterablePage} from "../../filterable-page/filterable-page";
import {Track} from "../../../shared/types/models/Track";
import {ActivatedRoute} from "@angular/router";

@Component({
    selector: 'top-tracks-page',
    templateUrl: './top-tracks-page.component.html',
    styleUrls: ['./top-tracks-page.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class TopTracksPageComponent extends FilterablePage<Track> implements OnInit {

    /**
     * TopTracksPageComponent Constructor.
     */
    constructor(private route: ActivatedRoute) {
        super(['name', 'album.name', 'album.artist.name']);
    }

    ngOnInit() {
        this.route.data.subscribe((data: {tracks: Track[]}) => {
            this.setItems(data.tracks);
        });
    }
}
