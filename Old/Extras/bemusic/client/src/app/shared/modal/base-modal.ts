import {Renderer2, ElementRef, EventEmitter, Output} from '@angular/core';

export class BaseModalClass {

    /**
     * ModalComponentRef destroy function. Added dynamically.
     */
    public destroy: Function;

    /**
     * Function called before modal close animation starts.
     */
    public beforeCloseAnimation: Function;

    /**
     * Any error this modal currently has.
     */
    public errors: any = {};

    /**
     * Fired when modal is opened.
     */
    @Output() public onShow = new EventEmitter();

    /**
     * Fired when modal is fully closed and destroyed.
     */
    @Output() public onClose = new EventEmitter();

    /**
     * Fired when modal 'confirm' button is clicked
     * and all needed operations are completed (xhr calls etc)
     */
    @Output() public onDone = new EventEmitter();

    /**
     * Should modal be closed when user clicks on backdrop.
     */
    protected closeModalOnBackdropClick = true;

    /**
     * Base z-index modals have applied via CSS.
     */
    protected baseZIndex = 11;

    /**
     * Whether modal http request is in progress currently.
     */
    public loading = false;

    /**
     * BaseModalClass Constructor.
     */
    constructor(protected elementRef: ElementRef, protected renderer: Renderer2) {
        setTimeout(() => {
            if (this.closeModalOnBackdropClick) {
                renderer.listen(this.elementRef.nativeElement, 'click', (e) => {
                    if (e.target.classList.contains('backdrop')) {
                        this.close();
                    }
                });
            }
        })
    }

    /**
     * Close this modal with animation.
     */
    public close(data = null) {
        this.elementRef.nativeElement.classList.remove('modal-visible');

        this.beforeCloseAnimation && this.beforeCloseAnimation();

        setTimeout(() => {
            this.elementRef.nativeElement.classList.add('hidden');
            this.onClose && this.onClose.emit(data);
            this.destroy && this.destroy();
        }, 200);
    }

    /**
     * Show this modal and focus first input element if there are any.
     */
    public show(params: Object = {}) {
        //increment z-index of each open modal, so active modal is properly on top in DOM
        this.renderer.setStyle(this.elementRef.nativeElement, 'z-index', this.baseZIndex + params['numOfModals']);

        //add "modal" class to modal element for proper styling
        this.renderer.addClass(this.elementRef.nativeElement, 'modal');

        //add "modal-active" class to root element (DOM body) for proper styling
        if (params['placeholder']) {
            this.renderer.addClass(params['placeholder'].rootEl, 'modal-active');
        }

        //remove "hidden" class so modal is animated in
        this.renderer.removeClass(this.elementRef.nativeElement, 'hidden');

        //make modal content visible after modal animation is complete
        requestAnimationFrame(() => {
            this.renderer.addClass(this.elementRef.nativeElement, 'modal-visible');
        });

        //focus input in the modal if there is any
        let input = this.elementRef.nativeElement.querySelector('.focus');

        setTimeout(() => {
            input && input.focus();
        }, 300);

        this.onShow && this.onShow.emit();
    }

    /**
     * Emit done event and close this modal.
     */
    public done(data = null) {
        this.loading = false;
        this.onDone.emit(data);
        this.handleErrors();
        this.close();
    }

    /**
     * Format errors received from server for display.
     */
    public handleErrors(response?: Object) {
        //clear old errors if no response is specified
        if ( ! response) {
            this.errors = {};
            return;
        }

        this.errors = response['messages'];
        this.loading = false;
    }
}