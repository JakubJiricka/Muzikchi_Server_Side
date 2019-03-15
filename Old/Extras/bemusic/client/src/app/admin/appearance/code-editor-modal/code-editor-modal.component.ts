import {Component, ElementRef, Renderer2, ViewChild, ViewEncapsulation} from '@angular/core';
import {BaseModalClass} from "../../../shared/modal/base-modal";
import {utils} from "../../../shared/utils";

declare let ace;

@Component({
    selector: 'code-editor-modal',
    templateUrl: './code-editor-modal.component.html',
    styleUrls: ['./code-editor-modal.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class CodeEditorModalComponent extends BaseModalClass {
    @ViewChild('editor') editorEl: ElementRef;

    /**
     * Whether ace js library is being loaded currently.
     */
    public loading = false;

    /**
     * Ace editor instance.
     */
    private editor;

    /**
     * CodeEditorModalComponent Constructor.
     */
    constructor(
        protected elementRef: ElementRef,
        protected renderer: Renderer2,
        protected utils: utils,
    ) {
        super(elementRef, renderer);
    }

    /**
     * Show modal and init code editor.
     */
    public show(params: {contents?: string, language: string}) {
        this.initEditor(params.contents, params.language);
        super.show(params);
    }

    /**
     * Close modal and editor code editor contents.
     */
    public confirm() {
        super.done(this.editor.getValue());
    }

    /**
     * Initiate code editor with specified contents.
     */
    private initEditor(contents: string, language = 'javascript') {
        this.loading = true;

        this.utils.loadScript('assets/js/ace/ace.js').then(() => {
            this.editor = ace.edit(this.editorEl.nativeElement);
            this.editor.getSession().setMode('ace/mode/'+language);
            this.editor.setTheme('ace/theme/chrome');
            this.editor.$blockScrolling = Infinity;
            if (contents) this.editor.setValue(contents, 1);
            this.loading = false;
        });
    }
}
