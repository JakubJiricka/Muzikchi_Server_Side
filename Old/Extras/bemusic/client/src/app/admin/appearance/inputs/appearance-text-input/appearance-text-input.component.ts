import {Component, Input, ViewEncapsulation} from '@angular/core';
import {EditableField} from "../../appearance-editor/editable-field-type";
import {AppearancePreview} from "../../appearance-editor/appearance-preview.service";
import {AppearancePendingChanges} from "../../appearance-editor/appearance-pending-changes.service";

@Component({
    selector: 'appearance-text-input',
    templateUrl: './appearance-text-input.component.html',
    styleUrls: ['./appearance-text-input.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class AppearanceTextInputComponent {

    /**
     * Editable field this input is attached to.
     */
    @Input() field: EditableField;

    /**
     * AppearanceTextInputComponent Constructor.
     */
    constructor(
        public preview: AppearancePreview,
        private changes: AppearancePendingChanges,
    ) {}

    /**
     * Fired when editable field is focused.
     */
    public onFocus(field: EditableField) {
        this.preview.highlightElement(field.selector);
    }

    /**
     * Fired when editable field loses focus.
     */
    public onBlur() {
        this.preview.removeHighlight();
    }

    /**
     * Commit text input changes.
     */
    public commitChanges(field: EditableField) {
        this.preview.setSetting(field.key, field.value);
        this.changes.add(field.key, field.value);
    }
}