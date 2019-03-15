import {Component, Input, ViewEncapsulation} from '@angular/core';
import {UrlAwarePaginator} from "../url-aware-paginator.service";
import {Translations} from "../../../shared/translations/translations.service";

@Component({
    selector: 'pagination-controls',
    templateUrl: './pagination-controls.component.html',
    styleUrls:  ['./pagination-controls.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class PaginationControlsComponent  {

    /**
     * Paginator instance.
     */
    @Input() public paginator: UrlAwarePaginator;

    /**
     * Whether last page button should be hidden.
     */
    @Input() public hideLastPageBtn = false;

    /**
     * Internal itemsName model.
     */
    private _itemsName: string;

    /**
     * Translate specified itemsName.
     */
    @Input('itemsName')
    set itemsName(value: string) {
        this._itemsName = this.i18n.t(value);
    }

    /**
     * Getter for _itemsName.
     */
    get itemsName(): string {
        return this._itemsName;
    }

    /**
     * Number of items per page by default.
     */
    @Input() public defaultPerPage: number|string = 15;

    /**
     * Whether pagination controls should always be shown.
     */
    @Input() alwaysShow: boolean = false;

    /**
     * PaginationControlsComponent Constructor.
     */
    constructor(private i18n: Translations) {}

    /**
     * Check if pagination controls should be visible.
     */
    public shouldBeVisible() {
        if ( ! this.paginator || ! this.paginator.data) return;

        return this.alwaysShow ||
            this.paginator.params.total > this.paginator.params.perPage ||
            this.paginator.params.currentPage != 1 ||
            this.paginator.params.perPage != this.defaultPerPage;
    }
}
