import {Component, OnInit, ViewEncapsulation} from "@angular/core";
import {ActivatedRoute} from "@angular/router";
import {SettingsState} from "./settings-state.service";
import {Settings} from "../../shared/settings.service";

@Component({
    selector: 'settings',
    templateUrl: './settings.component.html',
    styleUrls: ['./settings.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class SettingsComponent implements OnInit {

    constructor(
        private settings: Settings,
        private route: ActivatedRoute,
        private state: SettingsState,
    ) {}

    ngOnInit() {
        this.route.data.subscribe(data => {
            this.state.setAll(data['settings']);
        });
    }
}
