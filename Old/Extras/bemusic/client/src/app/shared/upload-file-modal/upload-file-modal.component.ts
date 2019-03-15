import {Component, ElementRef, Renderer2, ViewEncapsulation} from '@angular/core';
import {BaseModalClass} from "../modal/base-modal";
import {Uploads} from "../uploads.service";
import {Settings} from "../settings.service";

@Component({
    selector: 'upload-file-modal',
    templateUrl: './upload-file-modal.component.html',
    styleUrls: ['./upload-file-modal.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class UploadFileModalComponent extends BaseModalClass {

    /**
     * Params for uploading image.
     */
    public uploadParams: {uri: string, httpParams: object};

    /**
     * Name of current active tab.
     */
    public activeTab: string = 'upload';

    /**
     * Model for image url.
     */
    public linkModel: string;

    /**
     * File upload errors (if there are any).
     */
    public errors: Object|boolean;

    /**
     * InsertImageModal Constructor.
     */
    constructor(
        protected elementRef: ElementRef,
        protected renderer: Renderer2,
        private uploads: Uploads,
        private settings: Settings
    ) {
        super(elementRef, renderer);
    }

    /**
     * Show the modal.
     */
    public show(params: {uri: string, httpParams: object}) {
        this.uploadParams = params;
        super.show(params);
    }

    /**
     * Close modal and reset its state.
     */
    public close() {
        super.close();
        this.linkModel = null;
    }

    /**
     * Fired when user is done with this modal.
     */
    public confirm() {
        if (Object.keys(this.errors).length || ! this.linkModel) return;

        this.onDone.emit(this.linkModel);
        this.close();
    }

    /**
     * Set active tab to specified one.
     */
    public setActiveTab(name: string) {
        this.activeTab = name;
    }

    /**
     * Set specified link as link model.
     */
    public setLinkModel(link: string) {
        this.errors = false;

        this.validateImage(link).then(() => {
            this.linkModel = link;
        }).catch(() => {
            this.errors = {'*': 'The URL provided is not a valid image.'};
        });
    }

    /**
     * Open browser dialog for selecting files, upload files
     * and set linkModel to absolute url of uploaded image.
     */
    public uploadFiles(files: FileList) {
        this.errors = this.uploads.filesAreInvalid(files);
        if (this.errors) return;

        this.uploads.uploadFiles(files, this.uploadParams).subscribe(response => {
            this.linkModel = this.settings.getBaseUrl(true)+response.data[0].url;
            this.confirm();
        }, response => this.errors = response.messages);
    }

    /**
     * Check if image at specified url exists and is valid.
     */
    private validateImage(url: string): Promise<any> {
        if (this.uploadParams.httpParams['type'] === 'track') return new Promise(resolve => resolve(true));

        return new Promise((resolve, reject) => {
            let timeout = 500;
            let timer, img = new Image();

            //image is invalid
            img.onerror = img.onabort = () => {
                clearTimeout(timer);
                reject();
            };

            //image is valid
            img.onload = function () {
                clearTimeout(timer);
                resolve();
            };

            //reject image if loading it times out
            timer = setTimeout(function () {
                img = null; reject();
            }, timeout);

            img.src = url;
        });
    }
}