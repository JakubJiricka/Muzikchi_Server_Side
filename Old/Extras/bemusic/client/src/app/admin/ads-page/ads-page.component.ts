import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {Settings} from "../../shared/settings.service";
import {ToastService} from "../../shared/toast/toast.service";

@Component({
    selector: 'ads-page',
    templateUrl: './ads-page.component.html',
    styleUrls: ['./ads-page.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class AdsPageComponent implements OnInit {

    /**
     * Ads model.
     */
    public ads = {};

    /**
     * AdsPageComponent Constructor.
     */
    constructor(private settings: Settings, private toast: ToastService) {}

    ngOnInit() {
        this.hydrate();
    }

    /**
     * Save ads to the server.
     */
    public saveAds() {
        this.settings.save(this.ads).subscribe(() => {
            this.toast.show('Ads have been updated.');
        });
    }

    /**
     * Hydrate ads model.
     */
    private hydrate() {
        const all = this.settings.getAll();

        for (let name in all) {
            if (name.indexOf('ad_slot') > -1 || name === 'ads.disable') {
                this.ads[name] = all[name];
            }
        }
    }
}
