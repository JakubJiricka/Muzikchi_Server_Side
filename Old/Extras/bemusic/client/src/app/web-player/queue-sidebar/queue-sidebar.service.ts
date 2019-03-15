import {Injectable} from '@angular/core';

@Injectable()
export class QueueSidebar {

    /**
     * Whether queue sidebar is currently visible.
     */
    private visible = true;

    public isVisible() {
        return this.visible;
    }

    public show() {
        this.visible = true;
    }

    public hide() {
        this.visible = false;
    }

    public toggle() {
        this.visible = !this.visible;
    }

}
