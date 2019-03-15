import {KarmaTest} from "../../../../testing/karma-test";
import {InfiniteScrollDirective} from "./infinite-scroll.directive";
import {Component} from "@angular/core";

@Component({
    selector: 'infinite-scroll-host',
    template: '<div class="outer" style="height: 100px; overflow: auto" infinite-scroll (onInfiniteScroll)="loadFromBackend()" [infiniteScrollEnabled]="currentPage < lastPage"><div class="inner" style="height: 200px;"></div></div>'
})
export class InfiniteScrollHost {
    public currentPage = 1;
    public lastPage = 2;
    public loadFromBackend() {}
}

describe('InfiniteScrollDirective', () => {

    let testBed: KarmaTest<any>;
    let directive: InfiniteScrollDirective;

    beforeEach(() => {
        testBed = new KarmaTest({
            module: {
                declarations: [InfiniteScrollDirective, InfiniteScrollHost],
            },
            component: InfiniteScrollHost,
        });

        directive = testBed.getChildComponent(InfiniteScrollDirective);
    });

    it('loads from backend on element scroll', () => {
        testBed.fixture.detectChanges();

        let eventFired = false;
        directive.onInfiniteScroll.subscribe(() => eventFired = true);
        testBed.find('.outer').scrollTop = 90;
        testBed.dispatchEvent('div', 'scroll');
        expect(eventFired).toEqual(true);
    });

    it('does not load from backend if infinite scroll is disabled', () => {
        let eventFired = false;
        directive.onInfiniteScroll.subscribe(() => eventFired = true);

        directive.infiniteScrollEnabled = false;
        directive.onScroll(testBed.find('.outer'));

        expect(eventFired).toEqual(false);
    });

    it('loads from backend if user has scrolled near the element end', () => {
        let eventFired = false;
        directive.onInfiniteScroll.subscribe(() => eventFired = true);

        testBed.find('.outer').scrollTop = 90;
        testBed.fixture.detectChanges();
        directive.onScroll(testBed.find('.outer'));

        expect(eventFired).toEqual(true);
    });

    it('does not load from backend if user has not scrolled near the element end yet', () => {
        let eventFired = false;
        directive.onInfiniteScroll.subscribe(() => eventFired = true);

        testBed.find('.outer').scrollTop = 10;
        testBed.fixture.detectChanges();
        directive.onScroll(testBed.find('.outer'));

        expect(eventFired).toEqual(false);
    });

    it('loads from backend when "onInfiniteScroll" event is fired', () => {
        spyOn(testBed.get(InfiniteScrollHost), 'loadFromBackend');
        directive.onInfiniteScroll.emit();
        expect(testBed.get(InfiniteScrollHost).loadFromBackend).toHaveBeenCalledTimes(1);
    });

    it('can be disabled via @input property', () => {
        testBed.fixture.detectChanges();
        expect(directive.infiniteScrollEnabled).toEqual(true);

        testBed.get(InfiniteScrollHost).currentPage = 2;
        testBed.get(InfiniteScrollHost).lastPage = 2;
        testBed.fixture.detectChanges();
        expect(directive.infiniteScrollEnabled).toEqual(false);
    });
});