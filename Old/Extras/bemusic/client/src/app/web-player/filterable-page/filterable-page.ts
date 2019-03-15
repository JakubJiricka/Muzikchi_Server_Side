import {FormControl} from "@angular/forms";
import {Observable} from "rxjs/Observable";
import {utils} from "../../shared/utils";
import {ElementRef, OnDestroy, ViewChild} from "@angular/core";
import {Subscription} from "rxjs/Subscription";

export class FilterablePage<T> implements OnDestroy {
    @ViewChild('scrollContainer') scrollContainer: ElementRef;

    /**
     * Active subscriptions.
     */
    private subscriptions: Subscription[] = [];

    public order: string;

    /**
     * List of filtered items.
     */
    public filteredItems: T[] = [];

    /**
     * List of original items.
     */
    protected originalItems: T[] = [];

    /*
     * Query for filtering items.
     */
    public filterQuery = new FormControl();

    /**
     * FilterablePage Constructor.
     */
    constructor(protected filterProps: string[] = []) {
        this.bindToFilterQuery();
    }

    /**
     * Set specified items as filterable and original items.
     */
    protected setItems(items: T[]) {
        this.filteredItems = items;
        this.originalItems = items;
    }

    /**
     * Set specified items as filterable items.
     */
    protected setFilteredItems(items: T[]) {
        this.filteredItems = items;
    }

    /**
     * Append specified items to current items.
     */
    protected appendItems(items: T[]) {
        this.filteredItems = this.filteredItems.concat(items);
        this.originalItems = this.originalItems.concat(items);
    }

    /**
     * Prepend specified items to current items.
     */
    protected prependItems(items: T[]) {
        this.filteredItems = items.concat(this.filteredItems);
        this.originalItems = items.concat(this.originalItems);
    }

    /**
     * Remove specified items.
     */
    protected removeItems(items: T[]|T) {
        if ( ! Array.isArray(items)) items = [items];

        items.forEach(item => {
            this.filteredItems.splice(this.filteredItems.findIndex(i => item['id'] === i['id']), 1);
            this.originalItems.splice(this.originalItems.findIndex(i => item['id'] === i['id']), 1);
        });
    }

    /**
     * Filter items by specified query.
     */
    protected filter(query: string): T[]|Observable<T[]> {
        return this.originalItems.filter(item => {

            const props = this.filterProps.map(prop => {
                return utils.getObjectProp(item, prop);
            });

            return utils.strContains(props, query);
        });
    }

    /**
     * Sort items by specified object property.
     */
    public sort(prop: string, direction = 'asc', displayName?: string) {
        const order = displayName || prop;

        if (this.order === order) return;

        this.order = order;

        this.filteredItems.sort((a, b) => {
            if (utils.getObjectProp(a, prop) < utils.getObjectProp(b, prop)) {
                return -1;
            } else if (utils.getObjectProp(a, prop) > utils.getObjectProp(b, prop)) {
                return 1;
            } else {
                return 0;
            }
        });

        if (direction === 'desc') {
            this.filteredItems.reverse();
        }
    }

    /**
     * Filter items when user types into filter input.
     */
    protected bindToFilterQuery() {
        const sub = this.filterQuery.valueChanges
            .debounceTime(200)
            .distinctUntilChanged()
            .switchMap(query => {
                const filtered = query ? this.filter(query) : this.originalItems;
                return filtered instanceof Observable ? filtered : Observable.of(filtered);
            }).catch(() => {
                return Observable.of([]);
            }).subscribe(items => {
                this.setFilteredItems(items);
                this.triggerImagesLazyLoad();
            });

        this.subscriptions.push(sub);
    }

    /**
     * Manually trigger loading of lazy images on the page.
     */
    private triggerImagesLazyLoad() {
        if ( ! this.scrollContainer) return;
        let container = this.scrollContainer.nativeElement;
        container.scrollTop++;
    }

    ngOnDestroy() {
        this.subscriptions.forEach(subscription => {
            subscription.unsubscribe();
        });

        this.subscriptions = [];
    }
}
