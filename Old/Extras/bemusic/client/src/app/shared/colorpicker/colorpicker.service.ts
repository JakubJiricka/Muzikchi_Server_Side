import {Injectable} from '@angular/core';

@Injectable()
export class ColorPicker {

    /**
     * Show color picker and return promise
     * that resolves to user selected color.
     */
    public show(): Promise<number> {
        return new Promise(resolve => {
            let el = document.createElement('input');
            el.setAttribute('type', 'color');

            el.addEventListener('change', () => {
                resolve(el.value as any);
                el.remove();
            });

            el.click();
        });
    }
}
