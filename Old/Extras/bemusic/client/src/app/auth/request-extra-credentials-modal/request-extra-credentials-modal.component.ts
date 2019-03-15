import {Component, ElementRef, Renderer2, Output, EventEmitter, NgZone, ViewEncapsulation} from '@angular/core';
import {BaseModalClass} from "./../../shared/modal/base-modal";
import {Response} from "@angular/http";

@Component({
    selector: 'request-extra-credentials-modal',
    templateUrl: './request-extra-credentials-modal.component.html',
    encapsulation: ViewEncapsulation.None,
})
export class RequestExtraCredentialsModalComponent extends BaseModalClass {

    /**
     * Model for extra credentials.
     */
    public model: {email?: string, password?: string} = {};

    /**
     * What extra credentials need to be requested.
     */
    public credentialsToRequest: string[];

    /**
     * RequestExtraCredentialsModalComponent Constructor.
     */
    constructor(
        protected elementRef: ElementRef,
        protected renderer: Renderer2,
        private zone: NgZone
    ) {
        super(elementRef, renderer);
    }

    /**
     * Should we request specified credential from user.
     */
    public shouldCredentialBeRequested(name: string): boolean {
        return this.credentialsToRequest.indexOf(name) > -1;
    }

    /**
     * Set given params on component and show modal.
     */
    public show(params: Object) {
        this.zone.run(() => {
            this.credentialsToRequest = params['credentials'];
        });

        super.show(params);
    }

    /**
     * Submit extra credentials.
     */
    public confirm() {
        this.onDone.emit(Object.assign({}, this.model));
    }

    /**
     * Close modal and reset it to initial state.
     */
    public close() {
        this.model = {};
        this.errors = {};
        this.credentialsToRequest = [];
        super.close();
    }

    public handleErrors(response: Object) {
        //we need to request user extra credentials again, for example
        //if email address user supplied previously already exists
        //we might need to request password for account with that email
        if (response['messages']['email']) {
            this.credentialsToRequest.push('password');
        }

        this.zone.run(() => {
            super.handleErrors(response);
        });
    }
}