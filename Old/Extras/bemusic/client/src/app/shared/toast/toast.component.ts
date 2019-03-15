import {Component, ElementRef, Renderer2, ViewEncapsulation} from '@angular/core';

@Component({
    selector: 'toast',
    templateUrl: './toast.component.html',
    styleUrls: ['./toast.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class ToastComponent {

    /**
     * Toast message.
     */
    public message: string;

    /**
     * Available toast color types.
     */
    private types = ['default', 'success', 'error'];

    /**
     * Whether toast close button should be visible.
     */
    public showCloseButton = true;

    /**
     * ToastComponent Constructor.
     */
    constructor(private el: ElementRef, private renderer: Renderer2) {}

    /**
     * Show toast.
     */
    public show(hideDelay: number, type: string) {
        this.el.nativeElement.classList.add('toast-open');

        //remove old type classes from toast element
        this.types.forEach((type) => {
            this.renderer.removeClass(this.el.nativeElement, type);
        });

        if (this.types.indexOf(type) > -1) {
            this.renderer.addClass(this.el.nativeElement, type);
        }

        if (hideDelay) {
            setTimeout(() => {
                this.hide();
            }, hideDelay);
        }
    }

    /**
     * Hide toast.
     */
    public hide() {
        this.el.nativeElement.classList.remove('toast-open');
        this.el.nativeElement.classList.remove(this.types);
    }
}
