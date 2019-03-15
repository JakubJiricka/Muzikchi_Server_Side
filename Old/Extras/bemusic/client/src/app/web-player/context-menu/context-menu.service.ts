import {
    ComponentFactoryResolver, ComponentRef, Injectable, Injector, NgZone, Type,
    ViewContainerRef
} from '@angular/core';
import {NavigationStart, Router} from "@angular/router";
import {Artist} from "../../shared/types/models/Artist";
import {Album} from "../../shared/types/models/Album";
import {Track} from "../../shared/types/models/Track";
import Popper from 'popper.js'
import {BrowserEvents} from "../../shared/browser-events.service";

@Injectable()
export class ContextMenu {

    /**
     * Context menu placeholder.
     */
    public vcRef: ViewContainerRef;

    /**
     * Currently open context menu.
     */
    private current: ComponentRef<any>;

    /**
     * Context menu popper instance.
     */
    private popper: Popper;

    /**
     * Whether scroll, click and router events are bound already.
     */
    private eventsBound = false;

    /**
     * ModalService constructor.
     */
    constructor(
        private resolver: ComponentFactoryResolver,
        private router: Router,
        private browserEvents: BrowserEvents,
        private zone: NgZone,
        private injector: Injector
    ) {}

    /**
     * Show specified modal.
     */
    public show<T>(component: Type<T>, params: {item: Album|Artist|Track, extra?: object, type: string}, reference): ComponentRef<T> {
        this.bindToEvents();
        this.close();
        this.current = this.createMenuComponent(component, params);
        this.positionMenu(this.current.location.nativeElement, reference);
        return this.current;
    }

    /**
     * Position context menu using popper.js library.
     */
    private positionMenu(element: HTMLElement, reference) {
        setTimeout(() => {
            this.popper = new Popper(
                reference,
                element,
                {modifiers: {offset: { offset: '50%p' }}}
            );
        });
    }

    /**
     * Create instance of specified context menu component.
     */
    private createMenuComponent<T>(component: Type<T>, params?: {item: Album|Artist|Track}): ComponentRef<T> {
        let componentFactory = this.resolver.resolveComponentFactory(component),
            componentRef     = this.vcRef.createComponent(componentFactory, 0, this.injector);

        componentRef.instance['item'] = params.item;
        componentRef.instance['params'] = params;

        return componentRef;
    }

    /**
     * Close currently open context menu.
     */
    public close() {
        if ( ! this.current) return;

        //destroy context menu component
        this.current.destroy();
        this.current = null;

        //destroy context menu popper.js instance
        this.popper && this.popper.destroy();
        this.popper = null;
    }

    /**
     * Register context menu placeholder view reference.
     */
    public registerViewContainerRef(vcRef: ViewContainerRef) {
        this.vcRef = vcRef;
    }

    /**
     * Bind to events needed to close context menu on scroll, click and route change.
     */
    private bindToEvents() {
        if (this.eventsBound) return;

        //close menu on navigation to different route
        this.router.events.filter(e => e instanceof NavigationStart).subscribe(e => this.close());

        //close menu on scroll
        this.zone.runOutsideAngular(() => {
            document.addEventListener('scroll', e => {
                //don't close context menu if scrolling inside it
                if (e.target['classList'] && e.target['classList'].contains('playlists')) return;
                this.close();
            }, true);
        });

        //close menu when clicked outside it
        this.browserEvents.globalClick$.subscribe(e => {
            if (this.current && ! this.current.location.nativeElement.contains(e.target)) {
                this.close();
            }
        });

        this.eventsBound = true;
    }
}
