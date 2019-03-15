import {Component, Input, Output, EventEmitter, OnDestroy, ViewChild, AfterViewInit, ElementRef, ViewEncapsulation, Renderer2} from '@angular/core';
import {CurrentUser} from "../auth/current-user";
import {ModalService} from "../shared/modal/modal.service";
import {TinymceTextEditor} from "./editors/tinymce-text-editor.service";
import {HtmlTextEditor} from "./editors/html-text-editor.service";
import {utils} from "../shared/utils";
import {Uploads} from "../shared/uploads.service";
import {Settings} from "../shared/settings.service";
import {ColorPicker} from "../shared/colorpicker/colorpicker.service";
import {UploadFileModalComponent} from "../shared/upload-file-modal/upload-file-modal.component";

@Component({
    selector: 'text-editor',
    templateUrl: './text-editor.component.html',
    styleUrls: ['./text-editor.component.scss'],
    providers: [TinymceTextEditor, HtmlTextEditor],
    encapsulation: ViewEncapsulation.None,
})
export class TextEditorComponent implements OnDestroy, AfterViewInit {

    /**
     * TextArea element for visual text editor.
     */
    @ViewChild('visualArea') visualTextArea: ElementRef;

    /**
     * TextArea element for source text editor.
     */
    @ViewChild('sourceArea') sourceTextArea: ElementRef;

    /**
     * Model for editor source text area.
     */
    public sourceAreaModel: string;

    /**
     * Name of text editor that is currently active.
     */
    public activeEditor = 'visual';

    /**
     * Should advanced text editing controls be shown.
     */
    @Input() showAdvancedControls: boolean = false;

    /**
     * Whether only basic formatting options should be shown.
     */
    @Input() basic: boolean = false;

    /**
     * Minimum height for editor in pixels.
     */ 
    @Input() minHeight: number|string = 183;

    /**
     * Maximum height for editor in pixels. Contents will scroll after this height.
     */
    @Input() maxHeight: number = 530;

    /**
     * How to upload inline images.
     */
    @Input() inlineUploadType: string;

    /**
     * Fired when text editor contents change.
     */
    @Output() onChange: EventEmitter<string> = new EventEmitter();

    /**
     * Fired when user uses ctrl+enter keybind while text editor is focused.
     */
    @Output() onCtrlEnter = new EventEmitter();

    /**
     * Fired when user selected files to upload from browser file upload dialog.
     */
    @Output() onFileUpload = new EventEmitter();

    /**
     * Text editor implementation instance.
     */
    private editor: any;

    /**
     * Create new TextEditor component instance.
     */
    constructor(
        private tinyMceEditor: TinymceTextEditor,
        private htmlEditor: HtmlTextEditor,
        private uploads: Uploads,
        public currentUser: CurrentUser,
        private modal: ModalService,
        private colorpicker: ColorPicker,
        private settings: Settings,
        private renderer: Renderer2,
        public el: ElementRef,
    ) {

        if (this.settings.get('text_editor_driver', 'TinymceTextEditor') === 'TinymceTextEditor') {
            this.editor = this.tinyMceEditor;
        } else {
            this.editor = this.htmlEditor;
        }
    }

    ngAfterViewInit() {
        this.bootTextEditor();
    }

    /**
     * Reset the editor.
     */
    public reset() {
        this.editor.reset();
    }

    /**
     * Focus the editor.
     */
    public focus() {
        this.editor.focus();
    }

    /**
     * Check if editor has any undo actions left.
     */
    public hasUndo(): boolean {
        return this.editor.hasUndo();
    }

    /**
     * Check if editor has any redo actions left.
     */
    public hasRedo(): boolean {
        return this.editor.hasRedo();
    }

    /**
     * Queries the current state for specified text editor command.
     * For example if the current selection is "bold".
     */
    public queryCommandState(name: string): boolean|number {
        return this.editor.queryCommandState(name);
    }

    /**
     * Execute specified tinymce command.
     */
    public execCommand(name: string, value: string|number = null) {
        this.editor.execCommand(name, value);
    }

    /**
     * Insert information container of specified type into the editor.
     */
    public insertInfoContainer(type: string) {
        //TODO: refactor into shortcodes maybe if need more of similar buttons in the future
        //TODO: translate once angular translation service is available
        this.insertContents(
            `<div class="widget widget-${type}"><div class="title">${utils.ucFirst(type)}:</div><br></div>`
        );
    }

    /**
     * Show color picker and run specified command
     * with the color user has selected.
     */
    public showColorPicker(command: string) {
        this.colorpicker.show().then(color => {
            this.execCommand(command, color);
        })
    }

    /**
     * Show visual text editor.
     */
    public showVisualEditor() {
        if ( ! this.editor.tinymceInstance.contentAreaContainer || this.activeEditor === 'visual') return;

        this.activeEditor = 'visual';

        this.renderer.setStyle(this.editor.tinymceInstance.contentAreaContainer, 'display', 'block');
        this.renderer.setStyle(this.sourceTextArea.nativeElement, 'display', 'none');

        this.editor.focus();
        this.editor.setContents(this.sourceAreaModel);
    }

    /**
     * Show source text editor.
     */
    public showSourceEditor() {
        if ( ! this.editor.tinymceInstance.contentAreaContainer || this.activeEditor === 'source') return;

        this.activeEditor = 'source';

        this.renderer.setStyle(this.sourceTextArea.nativeElement, 'height', this.editor.tinymceInstance.contentAreaContainer.offsetHeight+'px');
        this.renderer.setStyle(this.sourceTextArea.nativeElement, 'display', 'block');
        this.renderer.setStyle(this.editor.tinymceInstance.contentAreaContainer, 'display', 'none');

        this.sourceAreaModel = this.editor.getContents({source_view: true});
    }

    /**
     * Open dropdown for attaching a file or uploading a new one.
     */
    public openFileUploadDialog() {
        this.uploads.openUploadDialog().then(fileList => {
            this.onFileUpload.emit(fileList);
        });
    }

    /**
     * Open modal window for inserting inline image into editor.
     */
    public openInsertImageModal() {
        const params = {uri: 'images/static/upload', httpParams: {type: this.inlineUploadType}};
        this.modal.show(UploadFileModalComponent, params).onDone.subscribe(url => {
            if (url) {
                this.insertImage(url);
            }
        })
    }

    /**
     * Get current text editor contents.
     */
    public getContents(): string {
        return this.editor.getContents();
    }

    /**
     * Overwrite text editor contents with specified content.
     */
    public setContents(contents: string) {
        this.editor.setContents(contents);
    }

    /**
     * Insert specified contents at the end of text editor.
     */
    public insertContents(contents) {
        this.editor.insertContents(contents);
    }

    /**
     * Insert specified image inline into text editor.
     */
    public insertImage(url: string) {
        this.editor.insertImage(url);
    }

    ngOnDestroy() {
        this.destroyEditor();
    }

    /**
     * Destroy text editor instance.
     */
    public destroyEditor() {
        this.editor.destroyEditor();
    }

    /**
     * Bootstrap active text editor implementation.
     */
    private bootTextEditor() {
        this.editor.setConfig({
            textAreaEl: this.visualTextArea,
            editorEl: this.el,
            minHeight: this.minHeight,
            maxHeight: this.maxHeight,
            onChange: this.onChange,
            onCtrlEnter: this.onCtrlEnter,
            showAdvancedControls: this.showAdvancedControls,
        });
    }
}
