import {AfterViewInit, Directive, ElementRef, OnDestroy} from '@angular/core';
import {Translations} from "./translations.service";
import {Settings} from "../settings.service";
import {Subscription} from "rxjs/Subscription";

@Directive({
    selector: '[trans], [trans-placeholder], [trans-title]'
})
export class TranslateDirective implements AfterViewInit, OnDestroy {

    /**
     * Active subscriptions for this directive.
     */
    private subscriptions: Subscription[] = [];

    /**
     * TranslateDirective Constructor.
     */
    constructor(
        private el: ElementRef,
        private i18n: Translations,
        private settings: Settings
    ) {}

    ngAfterViewInit() {
        if ( ! this.settings.get('i18n.enable')) return;
        let sub = this.i18n.localizationChange.subscribe(() => this.translate());
        this.translate();
        this.subscriptions.push(sub);
    }

    /**
     * Translate element.
     */
    private translate() {
        let el = this.el.nativeElement;

        //don't translate if element has
        //any other content besides text
        if (el.children.length) return;

        //translate placeholder
        if (el.getAttribute('placeholder')) {
            let key = el.getAttribute('placeholder');
            el.setAttribute('placeholder', this.i18n.t(key));
        }

        //translate html5 title
        else if (el.getAttribute('title')) {
            let key = el.getAttribute('title');
            el.setAttribute('title', this.i18n.t(key));
        }

        //translate node text content
        else {
            let key = el.textContent;
            el.textContent = this.i18n.t(key);
        }
    }

    ngOnDestroy() {
        this.subscriptions.forEach(subscription => {
            subscription && subscription.unsubscribe();
        });
        this.subscriptions = [];
    }
}