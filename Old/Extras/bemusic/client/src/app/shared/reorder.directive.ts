import {Directive, ElementRef, Renderer2, Input, Output, EventEmitter, AfterViewInit, NgZone} from '@angular/core';

@Directive({
    selector: '[reorder]',
})
export class ReorderDirective implements AfterViewInit {

    /**
     * Custom scrollbar directive instance.
     */
    @Input() private scrollContainer: HTMLElement;

    /**
     * CSS class name for items to be reordered.
     */
    @Input('reorder') itemClass: string;

    /**
     * Fired when item re-ordering is done (on drop).
     */
    @Output() reorder: EventEmitter<number[]> = new EventEmitter();

    /**
     * Element currently being dragged.
     */
    private draggedEl: HTMLElement;

    /**
     * Last ClientY coordinate. For determining if
     * user is dragging element up or down.
     */
    private previousClientY: number;

    /**
     * True if user clicked on handle that starts drag and drop.
     * Used to to only allow dragging of elements via handle.
     */
    private clickTargetIsHandle: any = false;

    /**
     * Holds drag and drop scrolling timeouts.
     */
    private timeouts = {scrollUp: null, scrollDown: null};

    /**
     * ReorderCategoriesDirective Constructor.
     */
    constructor(
        private el: ElementRef,
        private renderer: Renderer2,
        private zone: NgZone
    ) {}

    ngAfterViewInit() {
        this.refresh();
    }

    /**
     * Initiate or refresh drag and drop (sorting) functionality.
     */
    public refresh() {
        this.zone.runOutsideAngular(() => {
            let elements = this.el.nativeElement.querySelectorAll('.'+this.itemClass+'[data-id]');

            if ( ! elements || ! elements.forEach) return;

            elements.forEach(el => {
                this.renderer.setAttribute(el, 'draggable', 'true');
                this.renderer.listen(el, 'mousedown', this.handleMouseDown.bind(this));
                this.renderer.listen(el, 'dragstart', this.handleDragStart.bind(this));
            });
            
            this.renderer.listen(this.el.nativeElement, 'dragover', this.handleDragOver.bind(this));
            this.renderer.listen(this.el.nativeElement, 'dragenter', this.handleDragEnter.bind(this));
            this.renderer.listen(this.el.nativeElement, 'drop', this.handleDrop.bind(this));
            this.renderer.listen(this.el.nativeElement, 'dragend', this.handleDragEnd.bind(this));
        });
    }

    /**
     * Do any needed work on 'mouseDown' event.
     */
    private handleMouseDown(e) {
        this.clickTargetIsHandle = this.elementIsHandle(e.target);

        if (this.clickTargetIsHandle) {
            e.stopPropagation();
        }
    }

    /**
     * Do any needed work on 'dragStart' event.
     */
    private handleDragStart(e) {
        e.dataTransfer.effectAllowed = 'move';

        //prevent dragging unless user drags column by handle
        if (!this.clickTargetIsHandle) {
            e.preventDefault();
            return;
        }

        //cache element that is being dragged currently
        this.draggedEl = e.target;
        e.dataTransfer.setData('text/plain', 'dummy');
        this.renderer.addClass(this.draggedEl, 'draggable');
        this.renderer.addClass(this.el.nativeElement, 'dragging');

        this.draggedEl.style['will-change'] = 'transform';
    }

    /**
     * Do any needed work on 'dragOver' event.
     */
    private handleDragOver(e) {
        e.dataTransfer.dropEffect = 'move';

        if (e.preventDefault) e.preventDefault();

        this.scroll(e);
        console.log('over');
        return false;
    }

    /**
     * Do any needed work on 'dragEnter' event.
     */
    private handleDragEnter(e) {
        console.log('enter');
        let target = e.target.closest('.'+this.itemClass);

        if ( ! target || ! target.getAttribute('data-id') || target['animated'] || ! this.draggedEl) return;


        //move draggable element below or above element
        // currently under it, based on clientY coordinates
        if (target && target != this.draggedEl) {


            let draggedRect = this.draggedEl.getBoundingClientRect();
            let targetRect = target.getBoundingClientRect();

            if (this.previousClientY > e.clientY) {
                target.parentNode.insertBefore(this.draggedEl, target);
            } else {
                target.parentNode.insertBefore(this.draggedEl, target.nextSibling);
            }

            this.animate(targetRect, target);
            this.animate(draggedRect, this.draggedEl);

            this.previousClientY = e.clientY;
        }
    }

    /**
     * Do any needed work on 'drop' event.
     */
    private handleDrop(e) {
        if (e.stopPropagation) e.stopPropagation();

        this.emitReorderEvent();

        return false;
    }

    /**
     * Do any needed work on 'dragend' event.
     */
    private handleDragEnd(e) {
        if (e.stopPropagation) e.stopPropagation();

        if (this.draggedEl) {
            this.renderer.removeClass(this.draggedEl, 'draggable');
            this.renderer.removeClass(this.el.nativeElement, 'dragging');
            this.draggedEl = null;
        }

        this.stopScrolling();
    }

    /**
     * Scroll container up or down if draggable is touching top or bottom edge of container.
     */
    private scroll(e) {
        if ( ! this.scrollContainer) return;

        let y = e.clientY,
            container = this.el.nativeElement,
            containerRect = container.getBoundingClientRect(),
            top = containerRect.top + this.scrollContainer.scrollTop,
            bottom = this.scrollContainer.getBoundingClientRect().bottom;

        //scroll top
        if (y - top < 20) {
            clearInterval(this.timeouts.scrollUp);
            this.timeouts.scrollUp = setInterval(() => {
                this.scrollContainer.scrollTop = this.scrollContainer.scrollTop - 10;
            }, 24);

            //scroll bottom
        } else if (bottom - y < 20) {
            clearInterval(this.timeouts.scrollDown);
            this.timeouts.scrollDown = setInterval(() => {
                this.scrollContainer.scrollTop = this.scrollContainer.scrollTop + 10;
            }, 24);

            //stop scrolling
        } else {
            this.stopScrolling();
        }
    }

    /**
     * Clear drag and drop scrolling intervals.
     */
    private stopScrolling() {
        clearInterval(this.timeouts.scrollDown);
        clearInterval(this.timeouts.scrollUp);
    }

    /**
     * Animate categories re-ordering.
     */
    private animate(prevRect, target) {
        let ms = 150;
        let currentRect = target.getBoundingClientRect();

        this.renderer.setStyle(target, 'transition', 'none');
        this.renderer.setStyle(target, 'transform', 'translate3d('
            + (prevRect.left - currentRect.left) + 'px,'
            + (prevRect.top - currentRect.top) + 'px,0)'
        );

        target.offsetWidth; // repaint

        this.renderer.setStyle(target, 'transition', 'all ' + ms + 'ms');
        this.renderer.setStyle(target, 'transform', 'translate3d(0,0,0)');

        clearTimeout(target.animated);
        target.animated = setTimeout(() => {
            this.renderer.setStyle(target, 'transition', '');
            this.renderer.setStyle(target, 'transform', '');
            target.animated = false;
        }, ms);
    }

    /**
     * Check if specified element or its parent is a drag handle.
     */
    private elementIsHandle(el: HTMLElement | undefined): any {
        return true;
        // if ( ! el) return false;
        // return this.elementOrParentHasClass(el, 'drag-handle');
    }

    /**
     * Emit reorder event with new order for items.
     */
    private emitReorderEvent() {
        let ids = [];
        let elements = this.el.nativeElement.querySelectorAll('.' + this.itemClass);

        for (let i = 0; i < elements.length; i++) {
            let id = elements[i].getAttribute('data-id');
            id && ids.push(+id);
        }

        this.zone.run(() => {
            this.reorder.emit(ids);
        });
    }
}