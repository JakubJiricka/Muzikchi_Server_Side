import {Component, Input, OnInit, ViewEncapsulation} from '@angular/core';
import {EditableField} from "../../appearance-editor/editable-field-type";
import {AppearancePreview} from "../../appearance-editor/appearance-preview.service";
import {AppearancePendingChanges} from "../../appearance-editor/appearance-pending-changes.service";
import {CodeEditorModalComponent} from "../../code-editor-modal/code-editor-modal.component";
import {ModalService} from "../../../../shared/modal/modal.service";
import {AppearanceEditor} from "../../appearance-editor/appearance-editor.service";

@Component({
    selector: 'appearance-code-input',
    templateUrl: './appearance-code-input.component.html',
    styleUrls: ['./appearance-code-input.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class AppearanceCodeInputComponent implements OnInit {

    /**
     * Editable field this input is attached to.
     */
    @Input() field: EditableField;

    /**
     * AppearanceCodeInputComponent Constructor.
     */
    constructor(
        public preview: AppearancePreview,
        private changes: AppearancePendingChanges,
        private modal: ModalService,
        private appearance: AppearanceEditor
    ) {}

    ngOnInit() {
        this.addCodeToPreview(
            this.field.config.language,
            this.field.value,
        );
    }

    /**
     * Open code editor modal and commit resulting changes.
     */
    public openModal(field: EditableField) {
        let params = {contents: field.value, language: field.config.language};

        this.modal.show(CodeEditorModalComponent, params).onDone.subscribe(value => {
            this.addCodeToPreview(field.config.language, value);
            this.commitChanges(field, value);
        });
    }

    /**
     * Add custom css/js to preview iframe
     */
    private addCodeToPreview(type: string, contents: string) {
        let el = this.getOrCreateEl(type);
        el.innerHTML = contents;
    }

    /**
     * Create styles element for custom css
     * or return existing one if already created.
     */
    private getOrCreateEl(type = 'css'): HTMLElement {
        let el = this.preview.getElement('#editor-custom-'+type);

        if ( ! el) {
            el = this.preview.getDoc().createElement(type === 'css' ? 'style' : 'script');
            el.id = 'editor-custom-'+type;
            this.preview.getDoc().head.appendChild(el);
        }

        return el;
    }

    /**
     * Commit code field changes.
     */
    private commitChanges(field: EditableField, newValue: string) {
        field.value = newValue;
        this.changes.add(field.key, newValue);
    }
}