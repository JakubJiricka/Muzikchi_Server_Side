import {Component, Input, OnInit, ViewEncapsulation} from '@angular/core';
import {EditableField} from "../../appearance-editor/editable-field-type";
import {AppearancePreview} from "../../appearance-editor/appearance-preview.service";
import {AppearancePendingChanges} from "../../appearance-editor/appearance-pending-changes.service";
import {Translations} from "../../../../shared/translations/translations.service";

@Component({
    selector: 'appearance-list-input',
    templateUrl: './appearance-list-input.component.html',
    styleUrls: ['./appearance-list-input.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class AppearanceListInputComponent implements OnInit {

    /**
     * Editable field this input is attached to.
     */
    @Input() field: EditableField;

    constructor(
        public preview: AppearancePreview,
        private changes: AppearancePendingChanges,
        private i18n: Translations
    ) {}

    ngOnInit() {
        //decode field value json if needed
        if (this.field.value && ! Array.isArray(this.field.value)) {
            this.field.value = JSON.parse(this.field.value);
        }
    }

    /**
     * Add a new list item.
     */
    public addNewItem() {
        this.field.value.push({title: this.i18n.t('New Item'), content: this.i18n.t('Item content')});
        this.commitChanges();
    }

    /**
     * Remove specified list item.
     */
    public removeItem(item: object) {
        const i = this.field.value.indexOf(item);
        this.field.value.splice(i, 1);
        this.commitChanges();
    }

    /**
     * Highlight item that is being edited in preview.
     */
    public onFocus(selector: string, index) {
        this.preview.highlightElement(this.field.selector + ' ' + selector, index);
    }

    /**
     * Commit list item changes.
     */
    public commitChanges() {
        let value = JSON.stringify(this.field.value);
        this.preview.setSetting(this.field.key, value);
        this.changes.add(this.field.key, value);
    }
}