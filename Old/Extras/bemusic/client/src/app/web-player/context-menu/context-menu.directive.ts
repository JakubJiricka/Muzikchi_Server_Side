import {Directive, ElementRef, Input, OnInit, Renderer2, Type} from '@angular/core';
import {Album} from "../../shared/types/models/Album";
import {Artist} from "../../shared/types/models/Artist";
import {ContextMenu} from "./context-menu.service";
import {AlbumContextMenuComponent} from "../albums/album-context-menu/album-context-menu.component";
import {TrackContextMenuComponent} from "../tracks/track-context-menu/track-context-menu.component";
import {ArtistContextMenuComponent} from "../artists/artist-context-menu/artist-context-menu.component";
import {PlaylistTrackContextMenuComponent} from "../playlists/playlist-track-context-menu/playlist-track-context-menu.component";
import {PlaylistContextMenuComponent} from "../playlists/playlist-context-menu/playlist-context-menu.component";

@Directive({
    selector: '[contextMenu]'
})
export class ContextMenuDirective implements OnInit {

    /**
     * Params for displaying context menu for specified item.
     */
    @Input('contextMenu') params: {item: Artist|Album, type: 'artist'|'album'|'track'};

    /**
     * ContextMenuDirective Constructor.
     */
    constructor(
        private el: ElementRef,
        private renderer: Renderer2,
        private contextMenu: ContextMenu
    ) {}

    //close on scroll, sub in base menu component then unsub etc

    ngOnInit() {
         this.bindToContextMenuEvent();
    }

    /**
     * Show context menu for specified item on right click.
     */
    private bindToContextMenuEvent() {
        this.renderer.listen(this.el.nativeElement, 'contextmenu', e => {
            e.preventDefault();
            e.stopPropagation();

            this.contextMenu.show(
                this.getComponentType(this.params.type),
                this.params,
                this.getPopperReferenceObject(e)
            );
        });
    }

    /**
     * Get context menu component based on specified type.
     */
    private getComponentType(type: string): Type<any> {
        switch (type) {
            case 'artist':
                return ArtistContextMenuComponent;
            case 'album':
                return AlbumContextMenuComponent;
            case 'track':
                return TrackContextMenuComponent;
            case 'playlist':
                return PlaylistContextMenuComponent;
            case 'playlist-track':
                return PlaylistTrackContextMenuComponent;
        }
    }

    /**
     * Get popper.js reference object for showing
     * context menu at mouse click coordinates.
     */
    private getPopperReferenceObject(e: MouseEvent) {
        return {
            getBoundingClientRect: () => ({
                top: e.pageY,
                right: e.pageX,
                bottom: e.pageY,
                left: e.pageX,
                width: 0,
                height: 0,
            }),
            clientWidth: 0,
            clientHeight: 0,
        };
    }
}
