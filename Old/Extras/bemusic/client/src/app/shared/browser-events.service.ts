import {Injectable, EventEmitter, Renderer2} from '@angular/core';

@Injectable()
export class BrowserEvents {

    /**
     * Keyboard key codes map.
     */
    public readonly keyCodes = {
        enter: 13,
        space: 32,
        escape: 27,
        delete: 46,
        arrowUp: 38,
        arrowRight: 39,
        arrowDown: 40,
        arrowLeft: 37,
        letters: {
            s: 83,
            n: 78,
            a: 65,
            t: 84,
            b: 66,
            c: 67,
            o: 79,
            p: 80,
            r: 82,
            f: 70,
        },
    };

    /**
     * Click event for every element inside app component.
     */
    public globalClick$ = new EventEmitter();

    /**
     * Browser KeyDown event.
     */
    public globalKeyDown$: EventEmitter<KeyboardEvent> = new EventEmitter();

    /**
     * Angular Renderer service instance.
     */
    private renderer: Renderer2;

    /**
     * Create observables for document events.
     */
    public subscribeToEvents(el, renderer: Renderer2) {
        this.renderer = renderer;

        //document click event
        this.renderer.listen(el, 'click', e => this.globalClick$.emit(e));

        //document keyDown event
        document.addEventListener('keydown', e => {
            if (BrowserEvents.userIsTyping()) return;
            this.globalKeyDown$.emit(e);
        });
    }

    /**
     * Check if an input or textarea element is currently focused.
     */
    public static userIsTyping() {
        const inputs = ['input', 'textarea'],
              tagName = document.activeElement.tagName;

        if ( ! tagName) return true;

        return inputs.indexOf(tagName.toLowerCase()) > -1;
    }
}