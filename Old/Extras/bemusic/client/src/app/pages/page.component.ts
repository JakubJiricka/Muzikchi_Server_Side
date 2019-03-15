import {Component, OnInit, ViewEncapsulation} from "@angular/core";
import {Pages} from "../admin/pages/pages.service";
import {ActivatedRoute, Router} from "@angular/router";
import {Page} from "../shared/types/models/Page";
import {DomSanitizer, SafeHtml} from "@angular/platform-browser";

@Component({
    selector: 'page',
    templateUrl: './page.component.html',
    styleUrls: ['./page.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class PageComponent implements OnInit {

    /**
     * Page model instance.
     */
    public page: Page = new Page;

    /**
     * Page body trusted by angular.
     */
    public body: SafeHtml;

    /**
     * PagesComponent Constructor.
     */
    constructor(
        private pages: Pages,
        private route: ActivatedRoute,
        private sanitizer: DomSanitizer,
        private router: Router,
    ) {}

    ngOnInit() {
        this.route.params.subscribe(params => {
            const id = params.id || this.route.snapshot.data.id;

            this.pages.get(id).subscribe(page => {
                this.page = page;
                this.body = this.sanitizer.bypassSecurityTrustHtml(page.body);
            }, () => {
                this.router.navigate(['/404'], {skipLocationChange: true});
            });
        })
    }
}
