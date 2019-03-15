import {Injectable} from '@angular/core';
import {ToastService} from "../../../shared/toast/toast.service";
import {ModalService} from "../../../shared/modal/modal.service";
import {ConfirmModalComponent} from "../../../shared/modal/confirm-modal/confirm-modal.component";
import {AppHttpClient} from "../../../shared/app-http-client.service";

@Injectable()
export class AppearancePendingChanges {

    /**
     * AppearancePendingChanges Constructor.
     */
    constructor(private http: AppHttpClient, private toast: ToastService, private modal: ModalService) {}

    /**
     * Changes that are yet to be saved to backend.
     */
    private changes = {};

    /**
     * Add a new change to the store.
     */
    public add(name: string, value: any) {
        this.changes[name] = value;
        console.log(this.changes);
    }

    /**
     * Save pending changes to backend.
     */
    public save() {
        this.http.post('admin/appearance', this.changes).subscribe(() => {
            this.changes = {};
            this.toast.show('Appearance Saved.');
        });
    }

    /**
     * Check if there are any pending changes.
     */
    public isEmpty() {
        return !Object.keys(this.changes).length;
    }

    /**
     * If there are any unsaved changes, confirm if user wants to leave the page.
     */
    public canDeactivate() {
        if (this.isEmpty()) return true;

        return new Promise(resolve => {
            let modal = this.modal.show(ConfirmModalComponent, {
                title: 'Close Appearance Editor',
                body: 'Are you sure you want to close appearance editor?',
                bodyBold: 'All unsaved changes will be lost.',
                ok: 'Close',
                cancel: 'Stay',
            });

            modal.onDone.subscribe(() => resolve(true));
            modal.onClose.subscribe(() => resolve(false));
        });
    }
}