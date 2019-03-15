import {Injectable} from '@angular/core';
import {DropdownComponent} from "./dropdown.component";
import {BrowserEvents} from "../browser-events.service";

@Injectable()
export class DropdownService {

    /**
     * List of all dropdowns that are currently rendered in the app.
     */
    private allDropdowns: DropdownComponent[] = [];

    /**
     * DropdownService Constructor.
     */
    constructor(private browserEvents: BrowserEvents) {
        this.browserEvents.globalClick$.subscribe(() => {
            this.closeAll();
        });
    }

    /**
     * Add a new dropdown to all dropdowns list.
     */
    public add(dropdown: DropdownComponent) {
        this.allDropdowns.push(dropdown);
    }

    /**
     * Remove dropdown from list of all dropdowns.
     */
    public remove(dropdown: DropdownComponent) {
        for (let i = 0; i < this.allDropdowns.length; i++) {
            if (this.allDropdowns[i] === dropdown) {
                this.allDropdowns.splice(i, 1);
            }
        }
    }

    /**
     * Close all currently existing dropdowns.
     */
    public closeAll() {
        this.allDropdowns.forEach(dropdown => {
            if (dropdown.isOpen) dropdown.close();
        });
    }
}