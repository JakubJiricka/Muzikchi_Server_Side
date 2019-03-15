import {Component, ViewEncapsulation} from "@angular/core";
import {CurrentUser} from "../auth/current-user";
import {Settings} from "../shared/settings.service";
import {AuthService} from "../auth/auth.service";

@Component({
    selector: 'admin',
    templateUrl: './admin.component.html',
    styleUrls: ['./admin.component.scss'],
    encapsulation: ViewEncapsulation.None,
})

export class AdminComponent {

    /**
     * Controls left column visibility.
     */
    public leftColumnIsHidden = false;

    constructor(
        public settings: Settings,
        public currentUser: CurrentUser,
        public auth: AuthService
    ) {}
}
