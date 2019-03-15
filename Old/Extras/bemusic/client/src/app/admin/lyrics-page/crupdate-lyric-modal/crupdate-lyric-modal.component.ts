import {Component, ElementRef, Renderer2, ViewChild, ViewEncapsulation} from '@angular/core';
import {Lyric} from "../../../shared/types/models/Lyric";
import {BaseModalClass} from "../../../shared/modal/base-modal";
import {Lyrics} from "../../../web-player/lyrics/lyrics.service";
import {Observable} from "rxjs/Observable";
import {TextEditorComponent} from "../../../text-editor/text-editor.component";
import {Track} from "../../../shared/types/models/Track";

@Component({
    selector: 'crupdate-lyric-modal',
    templateUrl: './crupdate-lyric-modal.component.html',
    styleUrls: ['./crupdate-lyric-modal.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class CrupdateLyricModalComponent extends BaseModalClass {
    @ViewChild('textEditor') textEditor: TextEditorComponent;

    /**
     * Track this lyric belong to.
     */
    private track = new Track();

    /**
     * Lyric model.
     */
    public lyric = new Lyric({text: ''});

    /**
     * CrupdateLyricModalComponent Constructor.
     */
    constructor(
        protected el: ElementRef,
        protected renderer: Renderer2,
        protected lyrics: Lyrics,
    ) {
        super(el, renderer);
    }

    /**
     * Show the modal.
     */
    public show(params: {lyric?: Lyric, track?: Track}) {
        if (params.lyric) this.lyric = params.lyric;
        if (params.track) this.track = params.track;
        super.show(params);
        this.textEditor.setContents(this.lyric.text);
        this.textEditor.focus();
    }

    /**
     * Confirm track creation.
     */
    public confirm() {
        this.loading = true;

        this.createOrUpdateLyrics().subscribe(lyric => {
            super.done(lyric);
            this.textEditor.destroyEditor();
        }, this.handleErrors.bind(this));
    }

    /**
     * Close the modal.
     */
    public close() {
        super.close();
        this.textEditor.destroyEditor();
    }

    /**
     * Create a new lyric or update existing one.
     */
    private createOrUpdateLyrics(): Observable<Lyric> {
        let request;

        if (this.lyric.id) {
            request = this.lyrics.update(this.lyric.id, {text: this.textEditor.getContents()});
        } else {
            const payload = {text: this.textEditor.getContents(), track_id: this.track.id};
            request = this.lyrics.create(payload);
        }

        return request;
    }
}
