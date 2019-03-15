import {Injectable} from '@angular/core';

declare var tinymce: any;

@Injectable()
export class HtmlTextEditor {

    /**
     * Tinymce text editor configuration.
     */
    private config: Object = {};

    /**
     * Text editor contents;
     */
    private contents: string = '';

    /**
     * Reset the editor.
     */
    public reset() {
        this.contents = '';
    }

    public focus() {
        //
    }

    /**
     * Check if editor has any undo actions left.
     */
    public hasUndo(): boolean {
        return false;
    }

    /**
     * Check if editor has any redo actions left.
     */
    public hasRedo(): boolean {
        return false;
    }

    /**
     * Queries the current state for specified editor command.
     * For example if the current selection is "bold".
     */
    public queryCommandState(name: string): boolean|number {
        return false;
    }

    /**
     * Execute specified editor command.
     */
    public execCommand(name: string, value: string|number = null) {
        //
    }

    /**
     * Get current tinymce contents.
     */
    public getContents(): string {
        return this.contents;
    }

    /**
     * Overwrite tinymce contents with specified content.
     */
    public setContents(contents: string) {
        this.contents = contents;
        this.config['onChange'] && this.config['onChange'].emit(this.contents);
    }

    /**
     * Insert specified contents at the end of tinymce.
     */
    public insertContents(contents) {
        this.contents+=contents;
        this.config['onChange'] && this.config['onChange'].emit(this.contents);
    }

    /**
     * Insert specified image inline into tinymce.
     */
    public insertImage(url: string) {
        this.insertContents('<img src="'+url+'"/>');
    }

    public setConfig(config: Object) {
        this.config = config;
    }

    /**
     * Destroy tinymce instance and reset this service to initial state.
     */
    public destroyEditor() {
       this.contents = null;
    }
}
