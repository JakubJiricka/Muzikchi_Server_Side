import {Component, ElementRef, Renderer2, ViewEncapsulation} from '@angular/core';
import {BaseModalClass} from "../base-modal";
import {Translations} from "../../translations/translations.service";

@Component({
    selector: 'confirm-modal',
    templateUrl: './confirm-modal.component.html',
    styleUrls: ['./confirm-modal.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class ConfirmModalComponent extends BaseModalClass {
    public title: string;
    public body: string;
    public bodyBold: string;
    public ok: string;
    public cancel: string = this.i18n.t('Close');

    /**
     * Data to pass through if user confirms the action.
     */
    private data: any;

    /**
     * ConfirmModalComponent Constructor.
     */
    constructor(
        protected elementRef: ElementRef,
        protected renderer: Renderer2,
        private i18n: Translations
    ) {
        super(elementRef, renderer);
    }

    /**
     * Close confirmation modal.
     */
    public close() {
        super.close();
        this.data = null;
    }

    /**
     * Show confirmation modal.
     */
    public show(params) {
        this.data = params['data'];

        this.title = this.i18n.t(params['title']);
        this.body = this.i18n.t(params['body']);
        this.bodyBold = this.i18n.t(params['bodyBold']);
        if (params['cancel']) this.cancel = this.i18n.t(params['cancel']);
        this.ok = this.i18n.t(params['ok']);

        super.show(params);
    }

    /**
     * Confirm the action.
     */
    public confirm() {
        super.done(this.data);
    }
}