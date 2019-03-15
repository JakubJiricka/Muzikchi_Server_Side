import {Directive, OnDestroy, Input, ElementRef, OnInit, NgZone} from '@angular/core';
import {Translations} from "../translations/translations.service";
import Tooltip from 'tooltip.js'

@Directive({
    selector: '[tooltip]',
})
export class TooltipDirective implements OnInit, OnDestroy {
    @Input('tooltip') tooltipText: string;
    @Input('tooltip-direction') tooltipDirection = 'bottom';

    /**
     * Tooltip.js instance.
     */
    private tooltip: Tooltip;

    /**
     * TooltipDirective Constructor.
     */
    constructor(
        private el: ElementRef,
        private i18n: Translations,
        private zone: NgZone
    ) {}

    ngOnInit() {
        this.zone.runOutsideAngular(() => {
            this.tooltip = new Tooltip(this.el.nativeElement, {
                placement: this.tooltipDirection,
                trigger: 'hover',
                title: this.i18n.t(this.tooltipText)
            });
        });
    }

    ngOnDestroy() {
        this.tooltip && this.tooltip.dispose();
    }
}
