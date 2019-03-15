import {AfterViewInit, Component, ViewChild, ViewEncapsulation} from '@angular/core';
import {Subscription} from "rxjs/Subscription";
import {Observable} from "rxjs/Observable";
import {AppHttpClient} from "../../../shared/app-http-client.service";
import {MailTemplate} from "../../../shared/types/models/MailTemplate";

@Component({
    selector: 'mail-template-preview',
    templateUrl: './mail-template-preview.component.html',
    styleUrls: ['./mail-template-preview.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class MailTemplatePreviewComponent implements AfterViewInit {
    @ViewChild('iframe') private iframe: {nativeElement: HTMLIFrameElement};

    /**
     * Preview iframe document.
     */
    private doc: Document;

    /**
     * Blade template render http call subscription.
     */
    private renderSub: Subscription;

    /**
     * Whether preview is currently loading.
     */
    public loading = false;

    /**
     * Renderer template's cache.
     */
    private cache = {};

    /**
     * MailTemplatePreviewComponent Constructor.
     */
    constructor(private http: AppHttpClient) {}

    /**
     * Called after component's view has been fully initialized.
     */
    ngAfterViewInit() {
        this.initIframe();
    }

    /**
     * Update preview with specified contents.
     */
    public update(template: {model: MailTemplate, html: string, plain: string}, type: 'html'|'plain') {
        let contents = template[type];

        //check cache first
        if (this.cache[contents]) {
            return this.replaceIframeContents(this.cache[contents], type);
        }

        this.loading = true;

        this.renderSub = this.renderMailTemplate(template.model.file_name, type, contents).subscribe(response => {
            this.replaceIframeContents(response.contents, type);
            this.cacheRenderedTemplate(contents, response.contents);
        }, () => this.replaceIframeContents(''));
    }

    /**
     * Render specified mail template on the server.
     */
    private renderMailTemplate(fileName: string, type: string, contents: string): Observable<{contents: string}> {
        //cancel previous render http call, if it's still in progress
        if (this.renderSub) this.renderSub.unsubscribe();

        return this.http.post('mail-templates/render', {contents, type, file_name: fileName});
    }

    /**
     * Cache specified rendered template.
     */
    private cacheRenderedTemplate(raw: string, rendered: string) {
        let keys = Object.keys(this.cache);

        //cache a maximum of 10 rendered templates
        if (keys.length > 10) {
            delete this.cache[keys[0]];
        }

        this.cache[raw] = rendered;
    }

    /**
     * Replace all iframe contents with specified ones.
     */
    private replaceIframeContents(newContents: string, type: 'html'|'plain' = 'html') {
        this.iframe.nativeElement.style.height = 'auto';

        this.doc.open();
        this.doc.write(newContents);
        this.doc.close();

        //set iframe height to its contents height
        this.iframe.nativeElement.style.height = this.doc.body.scrollHeight + 'px';
        this.doc.body.style.whiteSpace = type === 'html' ? 'initial' : 'pre';
        this.loading = false;
    }

    /**
     * Initiate preview iframe.
     */
    private initIframe() {
        this.doc = this.iframe.nativeElement.contentWindow.document;
        this.doc.body.style.overflow = 'hidden';
    }

}
