import {
    Component, ElementRef, Renderer2, ViewEncapsulation, ContentChild, Input, ContentChildren, QueryList, OnDestroy,
    AfterContentInit, EventEmitter, Output, NgZone
} from '@angular/core';
import {BrowserEvents} from "../browser-events.service";
import {Subscription} from "rxjs/Subscription";
import {DropdownService} from "./dropdown.service";
import Popper from 'popper.js'

@Component({
    selector: 'dropdown',
    templateUrl: './dropdown.component.html',
    styleUrls: ['./dropdown.component.scss'],
    encapsulation: ViewEncapsulation.None,
    host: {'[class.disabled]': 'disabled'}
})
export class DropdownComponent implements AfterContentInit, OnDestroy  {

    /**
     * Dropdown trigger element.
     */
    @Input() @ContentChild('trigger') protected trigger: ElementRef;

    /**
     * Dropdown menu element.
     */
    @ContentChild('menu') protected menu: ElementRef;

    /**
     * Origin relative to which dropdown should be positioned.
     */
    @Input() @ContentChild('origin') protected origin: HTMLElement;

    /**
     * List of dropdown item elements.
     */
    @Input() @ContentChildren('menuItem') menuItems: QueryList<ElementRef>;

    /**
     * Main input element of dropdown (if any).
     */
    @ContentChild('input') input: ElementRef;

    /**
     * In which direction relative to origin dropdown should be positioned.
     */
    @Input() public direction: string = 'bottom';

    /**
     * Whether dropdown should be automatically positioned relative to origin.
     */
    @Input() public autoPosition = true;

    /**
     * Whether dropdown should be disabled.
     */
    @Input() public disabled = false;

    /**
     * Fired when dropdown is first opened.
     */
    @Output() public onFirstOpen = new EventEmitter;

    /**
     * Fired when dropdown is closed.
     */
    @Output() public onClose = new EventEmitter();

    /**
     * Client rect for dropdown menu.
     */
    public menuClientRect: ClientRect;

    /**
     * Whether dropdown is currently open.
     */
    public isOpen: boolean = false;

    /**
     * Whether this dropdown has been opened at least once already.
     */
    protected hasBeenOpened: boolean = false;

    /**
     * Index of dropdown item that is currently focused.
     */
    protected focusedItemIndex: number = -1;

    /**
     * Dropdown item that is currently focused.
     */
    protected focusedItem: ElementRef;

    /**
     * Any active subscriptions this dropdown created.
     */
    protected subscriptions: Subscription[] = [];

    /**
     * DropdownComponent Constructor.
     */
    constructor (
        protected el: ElementRef,
        protected renderer: Renderer2,
        protected browserEvents: BrowserEvents,
        protected dropdownService: DropdownService,
        protected zone: NgZone,
    ) {}

    /**
     * Fired when view content is initiated.
     */
    ngAfterContentInit() {
        this.setOriginAndTrigger();
        this.initMenu();
        this.bindClickEvents();
        this.bindHoverEvents();
        this.bindKeyDownEvents();
        this.dropdownService.add(this);
    }

    /**
     * Fired before dropdown is destroyed.
     */
    ngOnDestroy() {
        this.dropdownService.remove(this);

        this.subscriptions.forEach(subscription => {
            subscription && subscription.unsubscribe();
        });
    }

    /**
     * Open dropdown menu.
     */
    public open() {
        if (this.isOpen) return;

        this.dropdownService.closeAll();
        this.positionMenu();
        this.fireOnFirstOpenEvent();
        this.isOpen = true;

        this.renderer.addClass(this.el.nativeElement, 'dropdown-open');
        this.input && this.input.nativeElement.focus();
    }

    /**
     * Close dropdown menu.
     */
    public close() {
        this.isOpen = false;
        this.renderer.removeClass(this.el.nativeElement, 'dropdown-open');
        this.onClose.emit();
    }

    /**
     * Position dropdown menu based on direction and origin
     */
    protected positionMenu() {
        if ( ! this.autoPosition) return;

        let rect = this.origin.getBoundingClientRect(), left, top;

        if (this.direction === 'right') {
            left = rect.left + rect.width; top = rect.top;
        }

        else if (this.direction === 'bottom') {
            left = rect.left; top = rect.top + rect.height;
        }

        //make sure dropdown doesn't overflow browser window left
        let leftOverflow = (this.menuClientRect.width + left + 5) - window.innerWidth;
        left = leftOverflow > 1 ? left - leftOverflow : left;

        //make sure dropdown doesn't overflow browser window bottom
        let bottomOverflow = (this.menuClientRect.height + top + 5) - window.innerHeight;
        top = bottomOverflow > 1 ? top - bottomOverflow : top;

        //position dropdown menu
        this.renderer.setStyle(this.menu.nativeElement, 'left', left + 'px');
        this.renderer.setStyle(this.menu.nativeElement, 'top', top + 'px');
    }

    /**
     * Set dropdown origin and trigger elements.
     */
    protected setOriginAndTrigger() {
        //if origin is not passed in, use this dropdown host element
        if ( ! this.origin) this.origin = this.el.nativeElement;

        //if trigger is not passed in, use this dropdown host element
        if ( ! this.trigger) this.trigger = this.el;

        //make sure origin is HtmlElement and not ElementRef
        if (this.origin['nativeElement']) {
            this.origin = this.origin['nativeElement'];
        }
    }

    /**
     * Get menu client rect and apply css transformations.
     */
    protected initMenu() {
        this.menuClientRect = this.menu.nativeElement.getBoundingClientRect();
        this.renderer.addClass(this.menu.nativeElement, 'transformed');
    }

    /**
     * Bind any needed click events.
     */
    protected bindClickEvents() {
        this.renderer.listen(this.el.nativeElement, 'click', e => {
            e.stopPropagation();

            //dropdown is disabled
            if (this.disabled) return;

            //we want to open dropdown on input click, if input is a trigger (autocomplete for example)
            //but we don't want to close dropdown on input click, regardless if input is trigger or not
            if (this.isOpen && (this.input && e.target === this.input.nativeElement)) return;

            //toggle dropdown on dropdown trigger click
            if (e.target === this.trigger.nativeElement || this.trigger.nativeElement.contains(e.target)) {
                return this.isOpen ? this.close() : this.open();
            }

            //close dropdown, if clicked anywhere else inside dropdown
            this.close();
        });
    }

    /**
     * Bind any needed hover events.
     */
    protected bindHoverEvents() {
        this.zone.runOutsideAngular(() => {
            //set currently focused item index on dropdown items hover
            this.menuItems.changes.subscribe(() => {
                if ( ! this.menuItems.length) return;

                this.menuItems.forEach(item => {
                    this.renderer.listen(item.nativeElement, 'mouseenter', (e) => {
                        this.menuItems.forEach((item, index) => {
                            if (item.nativeElement === e.target) {
                                this.focusedItemIndex = index;
                            }
                        })
                    })
                });
            });
        });
    }

    /**
     * Fire "onFirstOpen" event, if it hasn't already been fired.
     */
    protected fireOnFirstOpenEvent() {
        if ( ! this.hasBeenOpened) {
            this.onFirstOpen.emit();
            this.onFirstOpen.complete();
            this.hasBeenOpened = true;
        }
    }

    /**
     * Bind keydown events needed for dropdown.
     */
    protected bindKeyDownEvents() {
        let sub = this.browserEvents.globalKeyDown$.subscribe(e => {
            let handled = false;

            //if dropdown is closed, bail
            if ( ! this.isOpen) return;

            switch (e.keyCode) {
                case this.browserEvents.keyCodes.enter:
                    if ( ! this.focusedItem) break;
                    this.focusedItem.nativeElement.dispatchEvent(new Event('click'));
                    handled = true;
                    break;
                case this.browserEvents.keyCodes.escape:
                    this.close();
                    handled = true;
                    break;
                case this.browserEvents.keyCodes.arrowUp:
                    this.focusItem('previous');
                    handled = true;
                    break;
                case  this.browserEvents.keyCodes.arrowDown:
                    this.focusItem('next');
                    handled = true;
                    break;
                case  this.browserEvents.keyCodes.arrowRight:
                    //
                    handled = true;
                    break;
                case  this.browserEvents.keyCodes.arrowLeft:
                    //
                    handled = true;
                    break;
            }

            if (handled) {
                e.preventDefault();
                e.stopPropagation();
            }
        });

        this.subscriptions.push(sub);
    }

    /**
     * Focus next dropdown item in specified direction.
     */
    protected focusItem(direction: string) {
        //select next or previous focused item
        if (direction === 'next') {
            this.focusedItemIndex += 1;
        } else if (direction === 'previous') {
            this.focusedItemIndex -= 1;
        }

        //if we are trying to select previous from first, select last instead
        if (this.focusedItemIndex < 0) {
            this.focusedItemIndex = this.menuItems.length - 1;
        }

        //if we are trying to select next from last, select first instead
        if (this.menuItems.length < this.focusedItemIndex + 1) {
            this.focusedItemIndex = 0;
        }

        //remove focused class from all items and get item that needs to be focused
        this.menuItems.forEach((value, index) => {
            this.renderer.removeClass(value.nativeElement, 'focused');
            if (index === this.focusedItemIndex) this.focusedItem = value;
        });

        if ( ! this.focusedItem) return;

        //focus the item
        this.renderer.addClass(this.focusedItem.nativeElement, 'focused');
    }
}
