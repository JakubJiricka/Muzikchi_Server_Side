import {Component, ElementRef, Renderer2, ViewEncapsulation} from '@angular/core';
import {BaseModalClass} from "../../../shared/modal/base-modal";

@Component({
    selector: 'lyrics-modal',
    templateUrl: './lyrics-modal.component.html',
    styleUrls: ['./lyrics-modal.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class LyricsModalComponent extends BaseModalClass {

    /**
     * Lyrics text.
     */
    public lyrics: string;

    /**
     * LyricsModalComponent Constructor.
     */
    constructor(
        protected elementRef: ElementRef,
        protected renderer: Renderer2
    )
    {
        super(elementRef, renderer);
    }

    /**
     * Show confirmation modal.
     */
    public show(params: { lyrics: string }) {
        this.lyrics = params.lyrics;
        super.show(params);
    }
}