import {Component, OnDestroy, OnInit, ViewEncapsulation} from '@angular/core';
import {ConfirmModalComponent} from "../../shared/modal/confirm-modal/confirm-modal.component";
import {Pages} from "./pages.service";
import {ModalService} from "../../shared/modal/modal.service";
import {CurrentUser} from "../../auth/current-user";
import {UrlAwarePaginator} from "../pagination/url-aware-paginator.service";
import {DataTable} from "../data-table";
import {Page} from "../../shared/types/models/Page";

@Component({
    selector: 'pages',
    templateUrl: './pages.component.html',
    styleUrls: ['./pages.component.scss'],
    providers: [UrlAwarePaginator],
    encapsulation: ViewEncapsulation.None,
})
export class PagesComponent extends DataTable implements OnInit, OnDestroy {

    /**
     * PagesComponent Constructor.
     */
    constructor(
        public paginator: UrlAwarePaginator,
        private pages: Pages,
        private modal: ModalService,
        public currentUser: CurrentUser,
    ) {
        super();
    }

    /**
     * Fetch initial pages to display.
     */
    ngOnInit() {
        this.paginator.paginate('pages').subscribe((response: {data: Page[]}) => {
            this.items = response.data;
        });
    }

    ngOnDestroy() {
        this.paginator.destroy();
    }

    /**
     * Ask user to confirm deletion of selected pages
     * and delete selected pages if user confirms.
     */
    public confirmPagesDeletion() {
        this.modal.show(ConfirmModalComponent, {
            title: 'Delete Pages',
            body:  'Are you sure you want to delete selected pages?',
            ok:    'Delete'
        }).onDone.subscribe(() => this.deleteSelectedPages());
    }

    /**
     * Delete currently selected pages.
     */
    public deleteSelectedPages() {
        this.pages.delete(this.selectedItems).subscribe(() => {
            this.paginator.refresh();
            this.selectedItems = [];
        });
    }
}
