import {Injectable, ViewContainerRef} from '@angular/core';

@Injectable()
export class ModalPlaceholderService {

    /**
     * Modal placeholder.
     */
    public vcRef: ViewContainerRef;

    /**
     * Root application element.
     */
    public rootEl: HTMLElement;

    /**
     * Register modal placeholder view reference and root application element;
     */
    registerViewContainerRef(vcRef: ViewContainerRef, rootEl: HTMLElement): void {
        this.vcRef = vcRef;
        this.rootEl = rootEl;
    }
}