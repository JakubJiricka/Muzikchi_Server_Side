import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {WebPlayerUrls} from "../../web-player-urls.service";
import {Genre} from "../../../shared/types/models/Genre";
import {FilterablePage} from "../../filterable-page/filterable-page";
import {ActivatedRoute} from "@angular/router";
import {Settings} from "../../../shared/settings.service";

@Component({
    selector: 'popular-genres',
    templateUrl: './popular-genres.component.html',
    styleUrls: ['./popular-genres.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class PopularGenresComponent extends FilterablePage<Genre> implements OnInit {

    /**
     * PopularGenresComponent Constructor.
     */
    constructor(
        private route: ActivatedRoute,
        public urls: WebPlayerUrls,
        public settings: Settings
    ) {
        super(['name']);
    }

    ngOnInit() {
        this.route.data.subscribe((data: {genres: Genre[]}) => {
            this.setItems(data.genres);
        });
    }
}
