import {Directive, OnInit, ElementRef, Renderer2, OnDestroy, EventEmitter, Output} from '@angular/core';
import {Uploads} from "../uploads.service";

@Directive({
    selector: '[fileDropzone]',
})
export class FileDropzoneDirective implements OnInit, OnDestroy {
    @Output() onUpload = new EventEmitter();

    /**
     * FileDropzoneDirective Constructor.
     */
    constructor(private el: ElementRef, private renderer: Renderer2, private uploads: Uploads) {}

    ngOnInit() {
        this.renderer.listen(this.el.nativeElement, 'dragenter', this.handleDragEnter.bind(this));
        this.renderer.listen(this.el.nativeElement, 'dragover', this.handleDragOver.bind(this));
        this.renderer.listen(this.el.nativeElement, 'dragleave', this.handleDragLeave.bind(this));
        this.renderer.listen(this.el.nativeElement, 'drop', this.handleDrop.bind(this));
        this.renderer.listen(this.el.nativeElement, 'click', this.handleClick.bind(this));
    }

    /**
     * When dropzone is clicked.
     */
    private handleClick() {
        this.uploads.openUploadDialog().then(files => {
            this.onUpload.emit(files);
        });
    }

    /**
     * When file is hovering over dropzone.
     */
    public handleDragOver(e) {
        if (e.preventDefault) e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        return false;
    }

    /**
     * When file enters dropzone.
     */
    public handleDragEnter() {
        this.renderer.addClass(this.el.nativeElement, 'file-over-dropzone');
    }

    /**
     * When file leaves dropzone.
     */
    public handleDragLeave() {
        this.removeClassesFromDropzone();
    }

    /**
     * When file is dropped on dropzone.
     */
    public handleDrop(e) {
        e.stopPropagation();
        e.preventDefault();
        this.removeClassesFromDropzone();

        this.onUpload.emit(e.dataTransfer.files);
    }

    /**
     * Remove "hover" class from dropzone element.
     */
    private removeClassesFromDropzone() {
        this.renderer.removeClass(this.el.nativeElement, 'file-over-dropzone');
    }

    ngOnDestroy() {
        this.removeClassesFromDropzone();
    }
}