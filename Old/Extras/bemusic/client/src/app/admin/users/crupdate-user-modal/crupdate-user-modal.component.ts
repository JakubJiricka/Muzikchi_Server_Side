import {Component, ElementRef, Renderer2, ViewEncapsulation} from '@angular/core';
import {ToastService} from "../../../shared/toast/toast.service";
import {BaseModalClass} from "../../../shared/modal/base-modal";
import {User} from "../../../shared/types/models/User";
import {Users} from "../../../web-player/users/users.service";

@Component({
    selector: 'crupdate-user-modal',
    templateUrl: './crupdate-user-modal.component.html',
    styleUrls: ['./crupdate-user-modal.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class CrupdateUserModalComponent extends BaseModalClass {

    /**
     * User model.
     */
    public model: User;

    /**
     * If we are updating existing user or creating a new one.
     */
    public updating: boolean = false;

    /**
     * CrupdateUserModalComponent Constructor.
     */
    constructor(
        protected elementRef: ElementRef,
        protected renderer: Renderer2,
        public users: Users,
        private toast: ToastService
    ) {
        super(elementRef, renderer);
        this.resetState();
    }

    /**
     * Show the modal.
     */
    public show(params: {user?: User}) {
        this.resetState();

        if (params['user']) {
            this.updating = true;
            this.hydrateModel(params['user']);
        } else {
            this.updating = false;
        }

        super.show(params);
    }

    /**
     * Create a new user or update existing one.
     */
    public confirm() {
        let request, payload = this.getPayload();

        if (this.updating) {
            request = this.users.update(payload.id, payload);
        } else {
            request = this.users.create(payload);
        }

        request.subscribe(response => {
            this.close();
            this.onDone.emit(response.data);
            let action = this.updating ? 'updated' : 'created';
            this.toast.show('User has been '+action);
        }, this.handleErrors.bind(this));
    }

    /**
     * Close the modal.
     */
    public close() {
        this.resetState();
        super.close();
    }

    /**
     * Get payload for updating or creating a user.
     */
    private getPayload() {
        let payload = Object.assign({}, this.model) as any;
        payload.groups = payload.groups.map(group => group.id);
        return payload;
    }

    /**
     * Reset all modal state to default.
     */
    private resetState() {
        this.model = new User({groups: []});
        this.errors = {};
    }

    /**
     * Populate user model with given data.
     */
    private hydrateModel(user) {
        Object.assign(this.model, user);
    }
}