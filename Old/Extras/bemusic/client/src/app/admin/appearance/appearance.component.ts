import {Component, OnInit, ViewChild, ViewEncapsulation} from '@angular/core';
import {Router} from "@angular/router";
import {AppearanceEditor} from "./appearance-editor/appearance-editor.service";
import {AppearancePreview} from "./appearance-editor/appearance-preview.service";
import {AppHttpClient} from "../../shared/app-http-client.service";

@Component({
    selector: 'appearance',
    templateUrl: './appearance.component.html',
    styleUrls: ['./appearance.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class AppearanceComponent implements OnInit {
    @ViewChild('iframe') iframe: {nativeElement: HTMLIFrameElement};
    @ViewChild('selectedElBox') selectedElBox: {nativeElement: HTMLElement};

    /**
     * AppearanceComponent Constructor.
     */
    constructor(
        private http: AppHttpClient,
        public appearance: AppearanceEditor,
        private router: Router,
        public preview: AppearancePreview,
    ) {}

    ngOnInit() {
        this.initAppearanceEditor();
    }

    /**
     * Make specified panel active.
     */
    public openPanel(name: string) {
        this.appearance.activePanel = name;
    }

    /**
     * Close appearance editor.
     */
    public closeEditor() {
        this.router.navigate(['admin']);
    }

    /**
     * Initiate appearance editor service.
     */
    private initAppearanceEditor() {
        this.http.get('admin/appearance/values').subscribe(defaults => {
            this.appearance.init({
                defaultSettings: defaults,
                iframe: this.iframe.nativeElement,
                selectedBox: this.selectedElBox.nativeElement,
            });
        });
    }
}
