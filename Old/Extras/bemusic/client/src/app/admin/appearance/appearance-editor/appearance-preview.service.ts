import {Injectable, NgZone} from '@angular/core';
import {PreviewApp} from "../../../shared/preview-app.service";
import {Settings} from "../../../shared/settings.service";

@Injectable()
export class AppearancePreview {

    /**
     * Live preview iframe.
     */
    public iframe: HTMLIFrameElement;

    /**
     * Element used for displaying outline around selected element.
     */
    public selectedElBox: HTMLElement;

    /**
     * Preview element that is currently highlighted.
     */
    private highlightedElement: HTMLElement;

    /**
     * Whether preview is currently loading.
     */
    public isLoading = true;

    /**
     * Instance of angular in the preview iframe.
     */
    private previewApp: PreviewApp;

    /**
     * AppearancePreview Constructor.
     */
    constructor(private zone: NgZone, private settings: Settings) {}

    /**
     * Highlight element in live preview by specified selector.
     */
    public highlightElement(selector: string, index = 0) {
        if ( ! selector) return;

        this.highlightedElement = this.getElement(selector, index);
        if ( ! this.highlightedElement) return;

        let rect = this.highlightedElement.getBoundingClientRect();

        this.selectedElBox.style.width = rect.width + 'px';
        this.selectedElBox.style.height = rect.height + 'px';
        this.selectedElBox.style.top = rect.top + 'px';
        this.selectedElBox.style.left = rect.left + 'px';
        this.selectedElBox.style.borderRadius = this.highlightedElement.style.borderRadius;
    }

    /**
     * Remove highlight element box from view.
     */
    public removeHighlight() {
        this.selectedElBox.style.width = '0px';
        this.selectedElBox.style.height = '0px';
        this.selectedElBox.style.left = '-15px';
        this.selectedElBox.style.top = '-15px';
    }

    /**
     * Get element from preview iframe by specified selector.
     */
    public getElement(selector: string, index = 0): HTMLElement {
        return this.getDoc().querySelectorAll(selector)[index] as HTMLElement;
    }

    /**
     * Navigate preview iframe to specified route.
     */
    public navigate(route: string) {
        Promise.resolve().then(() => this.isLoading = true);

        this.previewApp.zone.run(() => {
            this.previewApp.router.navigate([route], {queryParamsHandling: 'preserve'}).then(() => {
                this.zone.run(() => this.isLoading = false);
            });
        });
    }

    /**
     * Navigate to default preview url.
     */
    public navigateToDefaultRoute() {
        this.navigate('/');
    }

    /**
     * Initiate preview iframe with default url.
     */
    private initIframe(): Promise<any> {
        return new Promise(resolve => {
            this.iframe.onload = () => resolve();
            this.iframe.src = this.settings.getBaseUrl() + '?preview=' + this.settings.csrfToken;
        });
    }

    /**
     * Get specified setting on preview iframe.
     */
    public getSetting(name: string) {
        return this.previewApp.settings.get(name);
    }

    /**
     * Set specified setting on preview iframe and re-render.
     */
    public setSetting(name: string, value: any) {
        this.previewApp.zone.run(() => {
            this.previewApp.settings.set(name, value, true);
        });
    }

    /**
     * Initiate appearance preview.
     */
    public init(params: {iframe: HTMLIFrameElement, selectedBox: HTMLElement}) {
        this.isLoading = true;
        this.iframe = params.iframe;
        this.selectedElBox = params.selectedBox;

        this.initIframe().then(() => {
            //app is already loaded inside the iframe
            if (this.iframe.contentWindow['previewApp']) {
                this.finalizeInit(this.iframe.contentWindow['previewApp']);
            }

            //app is not yet loaded, we need to wait for it
            else {
                let previewReady = new Promise(resolve => {
                    this.iframe.contentWindow['angularReadyResolve'] = resolve;
                });

                previewReady.then((previewApp: PreviewApp) => {
                    this.finalizeInit(previewApp);
                });
            }

            //reposition highlighted element outline on iframe scroll
            this.getDoc().addEventListener('scroll', () => {
                if ( ! this.highlightedElement) return;
                this.selectedElBox.style.top = this.highlightedElement.getBoundingClientRect().top + 'px';
            }, true);
        });
    }

    /**
     * Get preview iframe document element.
     */
    public getDoc(): HTMLDocument {
        return this.iframe.contentWindow.document;
    }

    /**
     * Finalize preview init when iframe angular app is ready.
     */
    private finalizeInit(previewApp: PreviewApp) {
        this.isLoading = false;
        this.previewApp = previewApp;
        this.blockNotAllowedRoutes();
    }

    /**
     * Block preview navigation to routes that are not allowed.
     */
    private blockNotAllowedRoutes() {
        this.previewApp.router.events.subscribe(event => {
            //check if it's a navigation start event
            if (event.toString().indexOf('NavigationStart') > -1) {
                //only allow navigation in preview within web player
                if (event['url'].indexOf('admin') > -1) {
                    let current = this.previewApp.router.url.split('?')[0];
                    this.previewApp.router.navigate([current], {queryParamsHandling: 'preserve'});
                }
            }
        })
    }
}
