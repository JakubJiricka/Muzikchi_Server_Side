import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {UserLibrary} from "../user-library.service";
import {ActivatedRoute} from "@angular/router";

@Component({
    selector: 'library-tracks',
    templateUrl: './library-tracks.component.html',
    styleUrls: ['./library-tracks.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class LibraryTracksComponent implements OnInit {

    /**
     * LibraryTracksComponent Constructor.
     */
    constructor(public library: UserLibrary, private route: ActivatedRoute) {}

    ngOnInit() {
        this.route.data.subscribe(data => {
            this.library.tracks.setPaginationResponse(data.pagination);
        });
    }

    /**
     * Load more tracks from user library.
     */
    public loadMore() {
        this.library.tracks.loadMore();
    }

    /**
     * Check if more tracks can be loaded via infinite load.
     */
    public canLoadMore() {
        return this.library.tracks.canLoadMoreTracks();
    }
}
