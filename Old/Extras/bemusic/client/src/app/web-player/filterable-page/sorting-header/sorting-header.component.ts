import {Component, ContentChildren, ElementRef, Input, QueryList, ViewEncapsulation} from '@angular/core';
import {Translations} from "../../../shared/translations/translations.service";

@Component({
    selector: 'sorting-header',
    templateUrl: './sorting-header.component.html',
    styleUrls: ['./sorting-header.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class SortingHeaderComponent {
    @ContentChildren('menuItem') menuItems: QueryList<ElementRef>;

    /**
     * Name of current sorting order.
     */
    private _name: string;

    get name() {
        return this._name;
    }

    @Input('name')
    set name(value: string) {
        this._name = this.i18n.t(value);
    }

    /**
     * SortingHeaderComponent
     */
    constructor(private i18n: Translations) {}
}
