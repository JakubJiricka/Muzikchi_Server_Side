import {Injectable, NgZone} from '@angular/core';
import {utils} from "../../shared/utils";
import {Settings} from "../../shared/settings.service";

declare let tinymce: any;

@Injectable()
export class TinymceTextEditor {

    private bootstrapPromise: Promise<any>|boolean;

    private bootstrapPromiseResolve: any;

    /**
     * Tinymce text editor configuration.
     */
    private config: Object;

    private tinymceInstance: any;

    constructor(private settings: Settings, private zone: NgZone, private utils: utils) {
        this.makeBootstrapPromise();
    }

    /**
     * Reset the editor.
     */
    public reset() {
        if ( ! this.editorIsReady()) return;
        this.tinymceInstance.setContent('');
        this.tinymceInstance.undoManager.clear();
    }

    public focus() {
        this.waitForEditor().then(() => {
            this.tinymceInstance.focus();
        });
    }

    /**
     * Check if tinymce has any undo actions left.
     */
    public hasUndo(): boolean {
        return this.editorIsReady() && this.tinymceInstance.undoManager.hasUndo();
    }

    /**
     * Check if tinymce has any redo actions left.
     */
    public hasRedo(): boolean {
        return this.editorIsReady() && this.tinymceInstance.undoManager.hasRedo();
    }

    /**
     * Queries the current state for specified tinymce command.
     * For example if the current selection is "bold".
     */
    public queryCommandState(name: string): boolean|number {
        return this.editorIsReady() &&  this.tinymceInstance.queryCommandState(name);
    }

    /**
     * Execute specified tinymce command.
     */
    public execCommand(name: string, value: string|number = null) {
        this.waitForEditor().then(() => {
            this.zone.run(() => {
                this.tinymceInstance.execCommand(name, false, value);
            });
        })
    }

    /**
     * Get current tinymce contents.
     */
    public getContents(params?: Object): string {
        if ( ! this.editorIsReady()) return '';

        return this.tinymceInstance.getContent(params);
    }

    /**
     * Overwrite tinymce contents with specified content.
     */
    public setContents(contents: string) {
       this.waitForEditor().then(() => {
           //TODO: figure out why tinymce is not initiated properly sometimes
           if ( ! this.tinymceInstance.undoManager) return;

           this.tinymceInstance.undoManager.transact(() => {
               this.tinymceInstance.setContent(contents);
           });

           this.tinymceInstance.selection.setCursorLocation();
           this.tinymceInstance.nodeChanged();
           this.tinymceInstance.execCommand('mceResize');
       });
    }

    /**
     * Insert specified contents at the end of tinymce.
     */
    public insertContents(contents) {
        this.waitForEditor().then(() => {
            this.tinymceInstance.execCommand('mceInsertContent', false, contents);
        });
    }

    /**
     * Insert specified image inline into tinymce.
     */
    public insertImage(url: string) {
        this.waitForEditor().then(() => {
            this.insertContents('<img src="'+url+'"/>');
            setTimeout(() => this.execCommand('mceAutoResize'), 500);
        });
    }

    private waitForEditor(): Promise<any> {
        //editor already bootstrapped
        if (this.tinymceInstance) return new Promise(resolve => resolve());

        //editor is still bootstrapping
        if (this.bootstrapPromise) return <Promise<any>>this.bootstrapPromise;
    }

    public setConfig(config: Object) {
        this.config = config;
        this.loadTinymce().then(() => {
            this.initTinymce();
        });
    }

    private editorIsReady(): boolean {
        return ! this.bootstrapPromise && this.tinymceInstance && this.tinymceInstance.undoManager;
    }

    private loadTinymce(): Promise<any> {
        return this.utils.loadScript('assets/js/tinymce/tinymce.min.js')
    }

    private initTinymce() {
        let config: any = {
            target: this.config['textAreaEl'].nativeElement,
            skin_url: this.settings.getBaseUrl()+'/assets/js/tinymce/skins/lightgray',
            plugins: ['link', 'codesample', 'autoresize'],
            branding: false,
            browser_spellcheck: true,
            autoresize_on_init: false,
            autoresize_max_height: this.config['maxHeight'],
            autoresize_bottom_margin: 15,
            autoresize_min_height: this.config['minHeight'],
            elementpath: false,
            statusbar: false,
            menubar: false,
            convert_urls: false,
            forced_root_block: false,
            element_format: 'html',
            body_class: 'editor-body',
            content_style: 'html {font-size: 62.5%;} .editor-body {font-size: 1.4rem; font-family:"Roboto", "Helvetica Neue", sans-serif; color: rgba(0,0,0,.87);}',
            content_css: ['https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,400italic'],
            toolbar: false,
            default_link_target: '_blank',
            link_assume_external_targets: true,
            target_list: false,
            link_title: false,
            image_dimensions: false,
            image_description: false,
            setup: editor => {
                this.tinymceInstance = editor;
                editor.on('change', () => this.config['onChange'].emit(editor.getContent()));

                editor.on('click', () => {
                    //need to run angular zone on editor (iframe) click
                    //so custom editor buttons are highlighted properly
                    this.zone.run(() => {});
                });

                editor.shortcuts.add('ctrl+13', 'desc', () => {
                    this.zone.run(() => {
                        this.config['onChange'].emit(editor.getContent());
                        this.config['onCtrlEnter'].emit();
                    });
                });
            },
            init_instance_callback: () => {
                this.bootstrapPromise = false;
                this.bootstrapPromiseResolve();
            }
        };

        if (this.config['showAdvancedControls']) {
            config['plugins'] = config.plugins.concat(['media', 'hr', 'visualblocks', 'visualchars', 'wordcount']);
            config['forced_root_block'] = 'p';
            config.statusbar = true;
            //config.autoresize_on_init = true;
            config['extended_valid_elements'] = "svg[*],use[*]";
            config.elementpath = true;
            config.content_css.push(this.settings.getBaseUrl()+'/assets/css/advanced-editor-styles.css');
        }

        if (this.config['minHeight'] === 'auto') {
            //parent element height - editor toolbar and footer height
            let height = this.config['editorEl'].nativeElement.parentNode.offsetHeight - 132;
            config['autoresize_min_height'] = height;
            config['autoresize_max_height'] = height;
        }

        tinymce.init(config);
    }

    /**
     * Destroy tinymce instance and reset this service to initial state.
     */
    public destroyEditor() {
        //catch error that sometimes occurs on EDGE when
        //trying to destroy editor that is no longer in the DOM
        try {
            if (this.tinymceInstance)  {
                this.tinymceInstance.remove();
            }

            this.tinymceInstance = null;
            this.makeBootstrapPromise();
        } catch(e) {
            //
        }
    }

    /**
     * Create a tinymce bootstrap promise.
     */
    private makeBootstrapPromise() {
        this.bootstrapPromise = new Promise(resolve => this.bootstrapPromiseResolve = resolve);
    }
}
