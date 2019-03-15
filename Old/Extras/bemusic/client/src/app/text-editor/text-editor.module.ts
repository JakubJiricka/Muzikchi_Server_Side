import {NgModule}           from '@angular/core';
import {TextEditorComponent} from "./text-editor.component";
import {SharedModule} from "../shared/shared.module";

@NgModule({
    imports:      [
        SharedModule
    ],
    declarations: [
        TextEditorComponent,
    ],
    exports: [
        TextEditorComponent,
    ],
})
export class TextEditorModule { }