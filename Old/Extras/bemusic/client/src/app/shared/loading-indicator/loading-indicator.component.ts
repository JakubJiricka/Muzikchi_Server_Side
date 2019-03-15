import {Component, Input, ElementRef, ViewEncapsulation, HostBinding} from '@angular/core';

@Component({
    selector: 'loading-indicator',
    styleUrls: ['./loading-indicator.component.scss'],
    template:
`<div class="spinner" *ngIf="isVisible">
  <div class="rect rect1"></div>
  <div class="rect rect2"></div>
  <div class="rect rect3"></div>
  <div class="rect rect4"></div>
  <div class="rect rect5"></div>
</div>`,
    encapsulation: ViewEncapsulation.None,
})
export class LoadingIndicatorComponent {
    @HostBinding('class.visible') @Input() isVisible = false;

    constructor(private el: ElementRef) {}

    public show() {
        this.isVisible = true;
    }

    public hide() {
        this.isVisible = false;
    }

    public toggle() {
        this.isVisible = !this.isVisible;
    }
}
