import {Injectable, ComponentFactoryResolver, Injector, Type} from '@angular/core';
import {ModalPlaceholderService} from "./modal-placeholder.service";
import {NavigationEnd, Router} from "@angular/router";
import {BrowserEvents} from "../browser-events.service";

@Injectable()
export class ModalService {

    /**
     * All currently active modals.
     */
    private activeModals = [];

    /**
     * ModalService constructor.
     */
    constructor(
        private router: Router,
        private injector: Injector,
        private browserEvents: BrowserEvents,
        private modalPlaceholder: ModalPlaceholderService,
        private componentFactoryResolver: ComponentFactoryResolver,
    ) {
        this.bindToRouterAndKeydownEvents();
    }

    /**
     * Show specified modal.
     */
    public show<T>(component: Type<T>, params?: Object, injector?: Injector): T {
        //get number of modals that are currently open
        let numOfModals = this.modalPlaceholder.vcRef.length;

        //create specified modal component
        let componentFactory = this.componentFactoryResolver.resolveComponentFactory(component),
            componentRef     = this.modalPlaceholder.vcRef.createComponent(componentFactory, 0, injector || this.injector);

        componentRef.instance['destroy'] = () => {
            componentRef.destroy();
        };

        componentRef.instance['beforeCloseAnimation'] = () => {
            this.modalPlaceholder.rootEl.classList.remove('modal-active');
        };

        componentRef.instance['show'](Object.assign({}, params, {numOfModals, placeholder: this.modalPlaceholder}));

        this.activeModals.push(componentRef.instance);

        return componentRef.instance;
    }

    /**
     * Check if any modals are currently open.
     */
    public anyOpen() {
        return this.modalPlaceholder.vcRef.length;
    }

    /**
     * Close all active modals.
     */
    public closeAll() {
        this.activeModals.forEach(modal => {
            modal.close();

            //wait for animation to complete
            setTimeout(() => {
                modal.destroy();
            }, 201);
        });
        this.activeModals = [];
    }

    /**
     * Close all modals on navigation to different route or ESC key press.
     */
    private bindToRouterAndKeydownEvents() {
        this.router.events.subscribe(e => {
            if (e instanceof NavigationEnd) {
                this.closeAll();
            }
        });

        this.browserEvents.globalKeyDown$.subscribe(e => {
            if (e.keyCode === this.browserEvents.keyCodes.escape) {
                this.closeAll();
            }
        });
    }
}