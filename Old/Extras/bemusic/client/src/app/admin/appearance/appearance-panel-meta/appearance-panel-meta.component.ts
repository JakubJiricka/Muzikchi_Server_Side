import {Component, EventEmitter, Input, Output, ViewEncapsulation} from '@angular/core';

@Component({
    selector: 'appearance-panel-meta',
    templateUrl: './appearance-panel-meta.component.html',
    styleUrls: ['./appearance-panel-meta.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class AppearancePanelMetaComponent {

    /**
     * Currently active appearance panel path.
     */
    @Input() path: string[] = [];

    /**
     * Fired when back button clicked.
     */
    @Output() backClick = new EventEmitter();

    /**
     * Prettify specified path name.
     */
    public prettifyName(string: string) {
        return string.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
    }

}
