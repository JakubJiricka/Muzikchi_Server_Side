import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {PlayerQueue} from "../player/player-queue.service";
import {Track} from "../../shared/types/models/Track";
import {Player} from "../player/player.service";
import {QueueSidebar} from "./queue-sidebar.service";
import {ContextMenu} from "../context-menu/context-menu.service";
import {TrackContextMenuComponent} from "../tracks/track-context-menu/track-context-menu.component";
import {Settings} from "../../shared/settings.service";

@Component({
    selector: 'queue-sidebar',
    templateUrl: './queue-sidebar.component.html',
    styleUrls: ['./queue-sidebar.component.scss'],
    encapsulation: ViewEncapsulation.None,
    host: {'[class.hide]': '!sidebar.isVisible()'}
})
export class QueueSidebarComponent implements OnInit {

    /**
     * QueueSidebarComponent Constructor.
     */
    constructor(
        public queue: PlayerQueue,
        public player: Player,
        public sidebar: QueueSidebar,
        private contextMenu: ContextMenu,
        public settings: Settings,
    ) {}

    ngOnInit() {
        if (this.settings.get('player.hide_queue')) {
            this.sidebar.hide();
        }
    }

    /**
     * Play specified track.
     */
    public playTrack(track: Track, index: number) {
        if (this.player.cued(track)) {
            this.player.play();
        } else {
            this.player.stop();
            this.queue.set(index);
            this.player.play();
        }
    }

    /**
     * Check if specified track is cued and playing.
     */
    public trackIsPlaying(track: Track) {
        return this.player.isPlaying() && this.player.cued(track);
    }

    /**
     * Show context menu for specified track.
     */
    public showContextMenu(track: Track, e: MouseEvent) {
        e.stopPropagation();
        this.contextMenu.show(TrackContextMenuComponent, {item: track, type: 'track'}, e.target);
    }
}
