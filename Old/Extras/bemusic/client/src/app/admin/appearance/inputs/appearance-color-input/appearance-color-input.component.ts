import {Component, Input, OnInit, ViewEncapsulation} from '@angular/core';
import {EditableField} from "../../appearance-editor/editable-field-type";
import {AppearancePreview} from "../../appearance-editor/appearance-preview.service";
import {AppearancePendingChanges} from "../../appearance-editor/appearance-pending-changes.service";
import {AppearanceEditor} from "../../appearance-editor/appearance-editor.service";
import {utils} from "../../../../shared/utils";

@Component({
    selector: 'appearance-color-input',
    templateUrl: './appearance-color-input.component.html',
    styleUrls: ['./appearance-color-input.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class AppearanceColorInputComponent implements OnInit {

    /**
     * Editable field this input is attached to.
     */
    @Input() field: EditableField;

    /**
     * Template of editable theme for the application.
     */
    private editableTheme: string;

    /**
     * All editable fields of type color.
     */
    private allColorFields: EditableField[] = [];

    /**
     * AppearanceColorInput Constructor.
     */
    constructor(
        public preview: AppearancePreview,
        private changes: AppearancePendingChanges,
        private appearance: AppearanceEditor
    ) {}

    /**
     * Init the component.
     */
    ngOnInit() {
        if ( ! this.field.value) this.field.value = this.field.defaultValue;
        this.allColorFields = this.getAllColorFields();
        this.editableTheme = this.appearance.getDefaultSetting('editable_theme');
        this.compileTheme(false);
    }

    /**
     * Compile a CSS theme using user selected values.
     */
    public compileTheme(shouldCommit = true) {
        //copy the theme so we don't edit original
        let theme = this.editableTheme;

        //replace color placeholders in theme with actual values
        this.allColorFields.forEach(field => {
            theme = theme.replace(new RegExp(field.key, 'g'), field.value);
        });

        //apply generated theme to preview
        let style = this.getOrCreateStyleEl();
        style.innerHTML = theme;

        //commit changes if needed
        if (shouldCommit) {
            this.commitChanges(theme);
        }

        return theme;
    }

    /**
     * Create styles element for custom css
     * or return existing one if already created.
     */
    private getOrCreateStyleEl(): HTMLStyleElement {
        let style = this.preview.getElement('#editor-custom-css') as HTMLStyleElement;

        if ( ! style) {
            style = this.preview.getDoc().createElement('style');
            style.id = 'editor-custom-css';
            style.type = 'text/css';
            this.preview.getDoc().head.appendChild(style);
        }

        return style;
    }

    /**
     * Get color changes that need to be persisted to backend.
     */
    public commitChanges(theme: string) {
        //get current color values
        let values = this.allColorFields.map(field => {
            return {name: field.key, value: field.value};
        });

        this.changes.add('colors', {themeValues: values, theme})
    }

    /**
     * Get all editable fields of color type.
     */
    private getAllColorFields(): EditableField[] {
        return utils.flattenArray(this.appearance.config.map(configItem => {
            return configItem.fields.filter(field => field.type === 'color');
        }));
    }
}