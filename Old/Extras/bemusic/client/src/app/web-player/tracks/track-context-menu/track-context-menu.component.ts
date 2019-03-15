import {Component, Injector, ViewEncapsulation} from '@angular/core';
import {Track} from "../../../shared/types/models/Track";
import {ContextMenuComponent} from "../../context-menu/context-menu.component";
import {Player} from "../../player/player.service";
import {UserLibrary} from "../../users/user-library/user-library.service";
import {SelectedTracks} from "../track-list/selected-tracks.service";
import {Lyrics} from "../../lyrics/lyrics.service";
import {LyricsModalComponent} from "../../lyrics/lyrics-modal/lyrics-modal.component";

@Component({
    selector: 'track-context-menu',
    templateUrl: './track-context-menu.component.html',
    styleUrls: ['./track-context-menu.component.scss'],
    encapsulation: ViewEncapsulation.None,
    host: {'class': 'context-menu'},
})
export class TrackContextMenuComponent extends ContextMenuComponent<Track> {

    /**
     * Params needed to render context menu.
     */
    public params: {selectedTracks?: SelectedTracks, playlistId?: number, type: string};

    /**
     * TrackContextMenuComponent Constructor.
     */
    constructor(
        protected player: Player,
        protected library: UserLibrary,
        protected injector: Injector,
        protected lyrics: Lyrics,
    ) {
        super(injector);
    }

    /**
     * Check if this track is in player queue.
     */
    public inQueue() {
        return this.player.queue.has(this.item);
    }

    /**
     * Remove track from player queue.
     */
    public removeFromQueue() {
        this.player.queue.remove(this.item);
        this.contextMenu.close();
    }

    /**
     * Check if track is in user's library.
     */
    public inLibrary() {
        return this.library.has(this.item);
    }

    /**
     * Remove track from user's library.
     */
    public removeFromLibrary() {
        this.library.remove(this.getTracks());
        this.contextMenu.close();
    }

    /**
     * Copy fully qualified album url to clipboard.
     */
    public copyLinkToClipboard() {
        super.copyLinkToClipboard('track');
    }

    /**
     * Check if multiple tracks are selected in track list.
     */
    public multipleTracksSelected() {
        return this.params.selectedTracks && this.params.selectedTracks.all().length > 1;
    }

    /**
     * Get tracks that should be used by context menu.
     */
    public getTracks(): Track[] {
        return this.getSelectedTracks() || [this.item];
    }

    /**
     * Go to track radio route.
     */
    public goToTrackRadio() {
        this.contextMenu.close();
        this.router.navigate(this.urls.trackRadio(this.item));
    }

    /**
     * Fetch lyrics and show lyrics modal.
     */
    public showLyricsModal() {
        this.state.loading = true;

        this.lyrics.get(this.item.id).finally(() => {
            this.state.loading = false;
        }).subscribe(lyric => {
            this.modal.show(LyricsModalComponent, {lyrics: lyric.text});
        }, () => {
            this.toast.show('Could not find lyrics for this song.');
        });
    }

    /**
     * Get image for context menu.
     */
    public getImage() {
        if ( ! this.item.album) return this.settings.getDefaultImage('album');
        return this.item.album.image;
    }

    /**
     * Get currently selected tracks, if any.
     */
    private getSelectedTracks() {
        if ( ! this.params.selectedTracks || this.params.selectedTracks.all().length <= 1) return;
        return this.params.selectedTracks.all();
    }
}
