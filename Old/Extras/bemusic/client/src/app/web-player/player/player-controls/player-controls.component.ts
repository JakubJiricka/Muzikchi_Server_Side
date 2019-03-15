import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {Player} from "../player.service";
import {FullscreenOverlay} from "../../fullscreen-overlay/fullscreen-overlay.service";
import {QueueSidebar} from "../../queue-sidebar/queue-sidebar.service";
import {Lyrics} from "../../lyrics/lyrics.service";
import {ModalService} from "../../../shared/modal/modal.service";
import {LyricsModalComponent} from "../../lyrics/lyrics-modal/lyrics-modal.component";
import {WebPlayerState} from "../../web-player-state.service";
import {Settings} from "../../../shared/settings.service";
import {ToastService} from "../../../shared/toast/toast.service";

@Component({
    selector: 'player-controls',
    templateUrl: './player-controls.component.html',
    styleUrls: ['./player-controls.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class PlayerControlsComponent implements OnInit {

    /**
     * Whether lyrics button should be hidden.
     */
    public shouldHideLyricsButton = false;

    /**
     * Whether video toggle button should be hidden.
     */
    public shouldHideVideoButton = false;

    /**
     * PlayerControlsComponent Constructor.
     */
    constructor(
        public player: Player,
        private lyrics: Lyrics,
        private modal: ModalService,
        public overlay: FullscreenOverlay,
        public queueSidebar: QueueSidebar,
        private webPlayerState: WebPlayerState,
        private settings: Settings,
        private toast: ToastService,
    ) {}

    ngOnInit() {
        this.shouldHideLyricsButton = this.settings.get('player.hide_lyrics');
        this.shouldHideVideoButton = this.settings.get('player.hide_video_button');
    }

    /**
     * Fetch lyrics and show lyrics modal.
     */
    public showLyricsModal() {
        const track = this.player.getCuedTrack();
        if ( ! track) return;

        this.webPlayerState.loading = true;

        this.lyrics.get(track.id).finally(() => {
            this.webPlayerState.loading = false;
        }).subscribe(lyric => {
            this.modal.show(LyricsModalComponent, {lyrics: lyric.text});
        }, () => {
            this.toast.show('Could not find lyrics for this song.');
        });
    }
}
