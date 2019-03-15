import {Directive, Input, Output, ElementRef, OnInit, Renderer2, EventEmitter} from '@angular/core';

@Directive({
    selector: '[infinite-scroll]',
})
export class InfiniteScrollDirective implements OnInit {

    /**
     * How much pixels from the bottom to load more items.
     */
    @Input() threshold: number|string = 50;

    /**
     * If infinite scroll is enabled.
     */
    @Input() infiniteScrollEnabled = true;

    /**
     * Element on which to listen for scroll event.
     */
    @Input() infiniteScrollEl: HTMLElement|string;

    /**
     * Fired when more items need to be loaded.
     */
    @Output() onInfiniteScroll = new EventEmitter();

    /**
     * InfiniteScrollDirective Constructor.
     */
    constructor(el: ElementRef, private renderer: Renderer2) {
        this.infiniteScrollEl = el.nativeElement;
    }

    ngOnInit() {
        //TODO: refactor this angular "way" once it implements
        //TODO: "useCapture" into renderer or hostListener

        if (this.infiniteScrollEl === 'document') {
            document.addEventListener('scroll', (e) => this.onScroll(e.target as HTMLElement), true);
        } else {
            this.renderer.listen(this.infiniteScrollEl, 'scroll', e => this.onScroll(e.target));
        }
    }

    /**
     * Fire onInfiniteScroll event if we are at the end
     * of the page and infinite scroll is not disabled.
     */
    public onScroll(target: HTMLElement) {
        if ( ! this.infiniteScrollEnabled) return;

        let offset = parseInt(this.threshold as string);

        if (target.scrollTop + target.offsetHeight >= target.scrollHeight - offset) {
            this.onInfiniteScroll.emit();
        }
    }
}