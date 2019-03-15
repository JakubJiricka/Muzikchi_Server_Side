import {Component, Input, ViewEncapsulation} from '@angular/core';
import {EditableField} from "../../appearance-editor/editable-field-type";
import {AppearancePreview} from "../../appearance-editor/appearance-preview.service";
import {AppearancePendingChanges} from "../../appearance-editor/appearance-pending-changes.service";
import {ModalService} from "../../../../shared/modal/modal.service";
import {UploadFileModalComponent} from "../../../../shared/upload-file-modal/upload-file-modal.component";

@Component({
    selector: 'appearance-image-input',
    templateUrl: './appearance-image-input.component.html',
    styleUrls: ['./appearance-image-input.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class AppearanceImageInputComponent {

    /**
     * Editable field this input is attached to.
     */
    @Input() field: EditableField;

    /**
     * AppearanceImageInputComponent Constructor.
     */
    constructor(
        public preview: AppearancePreview,
        private changes: AppearancePendingChanges,
        private modal: ModalService,
    ) {}

    /**
     * Open modal for changing specified editable field image.
     */
    public openModal(field: EditableField) {
        const params = {uri: 'images/static/upload', httpParams: {type: 'branding'}};

        this.modal.show(UploadFileModalComponent, params).onDone.subscribe(url => {
            this.updateValue(url);

            //re-position highlight element box after uploading image,
            //use timeout to wait until new image is loaded properly
            setTimeout(() => {
                this.preview.highlightElement(field.selector);
            }, 100);
        });
    }

    /**
     * Remove current editable field image.
     */
    public remove() {
        this.updateValue(null);
    }

    /**
     * Use default value for image field.
     */
    public useDefault() {
        this.updateValue(this.field.defaultValue);
    }

    /**
     * Update current image field value.
     */
    private updateValue(value: string) {
        this.commitChanges(this.field, value);
        this.preview.setSetting(this.field.key, value);
    }

    /**
     * Commit image changes.
     */
    private commitChanges(field: EditableField, newValue: any) {
        field.value = newValue;
        this.changes.add(field.key, newValue);
    }
}