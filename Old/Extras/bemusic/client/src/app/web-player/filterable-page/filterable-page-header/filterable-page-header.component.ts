import {ChangeDetectionStrategy, Component, ElementRef, Input, OnDestroy, OnInit, ViewChild, ViewEncapsulation} from '@angular/core';
import {FormControl} from "@angular/forms";
import {BrowserEvents} from "../../../shared/browser-events.service";
import {Subscription} from "rxjs/Subscription";

@Component({
    selector: 'filterable-page-header',
    templateUrl: './filterable-page-header.component.html',
    styleUrls: ['./filterable-page-header.component.scss'],
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class FilterablePageHeaderComponent implements OnInit, OnDestroy {
    @ViewChild('filterInput') filterInput: ElementRef;

    /**
     * Active component subscriptions.
     */
    private subscriptions: Subscription[] = [];

    /**
     * Form control for filter input;
     */
    @Input() public filterQuery: FormControl;

    /**
     * FilterablePageHeaderComponent Constructor.
     */
    constructor(private browserEvents: BrowserEvents) {}

    ngOnInit() {
        this.initKeybinds();
    }

    ngOnDestroy() {
        this.subscriptions.forEach(subscription => {
            subscription.unsubscribe();
        });

        this.subscriptions = [];
    }

    /**
     * Initiate volume keyboard shortcuts.
     */
    private initKeybinds() {
        const sub = this.browserEvents.globalKeyDown$.subscribe((e: KeyboardEvent) => {
            //ctrl+f - focus search bar
            if (e.ctrlKey && e.keyCode === this.browserEvents.keyCodes.letters.f) {
                this.filterInput.nativeElement.focus();
                e.preventDefault();
            }
        });

        this.subscriptions.push(sub);
    }
}
