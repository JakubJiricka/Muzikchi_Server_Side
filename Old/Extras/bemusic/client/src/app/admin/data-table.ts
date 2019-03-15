import {FormControl} from "@angular/forms";
import {UrlAwarePaginator} from "./pagination/url-aware-paginator.service";
import {utils} from "../shared/utils";

export class DataTable {

    /**
     * Form control for DataTable search input.
     */
    public searchQuery = new FormControl();

    /**
     * All available items.
     */
    public items: any[];

    /**
     * Whether all items are currently selected.
     */
    public allItemsSelected: boolean = false;

    /**
     * All currently selected items.
     */
    public selectedItems: number[] = [];

    /**
     * Random string prefix for this datable.
     */
    public prefix;

    /**
     * DataTable paginator instance.
     */
    public paginator: UrlAwarePaginator;

    /**
     * DataTable Constructor.
     */
    constructor() {
        this.prefix = utils.randomString();

        this.searchQuery.valueChanges
            .debounceTime(400)
            .distinctUntilChanged()
            .subscribe(query => {
                this.paginator.refresh({query});
            });
    }

    /**
     * Check if given item is currently selected.
     */
    public isItemSelected(item: number) {
        return this.selectedItems.indexOf(item) > -1;
    }

    /**
     * Selected or deselect specified item.
     */
    public toggleSelectedItem(item: number) {
        let index = this.selectedItems.indexOf(item);

        if (index > -1) {
            this.selectedItems.splice(index, 1);
        } else {
            this.selectedItems.push(item);
        }
    }

    /**
     * Select or de-select all items.
     */
    public toggleAllSelectedItems() {
        if (this.allItemsSelected) {
            this.selectedItems = [];
        } else {
            this.selectedItems = this.items.map((item: any) => item.id);
        }

        this.allItemsSelected = !this.allItemsSelected;
    }

    /**
     * Deselect all currently selected items.
     */
    public deselectAllItems() {
        this.selectedItems = [];
        this.allItemsSelected = false;
    }
}
