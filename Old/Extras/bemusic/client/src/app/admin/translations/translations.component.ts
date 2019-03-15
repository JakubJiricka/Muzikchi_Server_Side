import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {ToastService} from "../../shared/toast/toast.service";
import {ConfirmModalComponent} from "../../shared/modal/confirm-modal/confirm-modal.component";
import {ModalService} from "../../shared/modal/modal.service";
import {FormControl} from "@angular/forms";
import {utils} from "../../shared/utils";
import {Translations} from "../../shared/translations/translations.service";
import {CrupdateLocalizationModalComponent} from "./crupdate-localization-modal/crupdate-localization-modal.component";
import {ActivatedRoute} from "@angular/router";
import {CurrentUser} from "../../auth/current-user";
import {Localization} from "../../shared/types/models/Localization";
import {Settings} from "../../shared/settings.service";
import {Localizations} from "../../shared/translations/localizations.service";

@Component({
    selector: 'translations',
    templateUrl: './translations.component.html',
    styleUrls: ['./translations.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class TranslationsComponent implements OnInit {

    /**
     * Control for categories search field.
     */
    public searchQuery = new FormControl();

    /**
     * Currently selected language.
     */
    public selectedLocalization: Localization = new Localization;

    /**
     * Filtered translation lines of selected localization.
     */
    public translations = {};

    /**
     * All user created localizations.
     */
    public localizations: Localization[] = [];

    /**
     *LocalizationsComponent Constructor.
     */
    constructor(
        private toast: ToastService,
        private modal: ModalService,
        private settings: Settings,
        private i18n: Translations,
        private route: ActivatedRoute,
        public currentUser: CurrentUser,
        private localizationsApi: Localizations
    ) {}

    ngOnInit() {
        this.bindSearchQuery();

        this.route.data.subscribe(data => {
            this.setLocalizations(data['localizations']);
        });
    }

    /**
     * Set default localization language for the site.
     */
    public setDefaultLocalization() {
        this.localizationsApi.setDefault(this.selectedLocalization.name).subscribe(() => {
            this.toast.show('Default Localization Changed');
        });
    }

    /**
     * Update currently selected localization.
     */
    public updateLocalization() {
        this.localizationsApi.update(this.selectedLocalization.id, this.selectedLocalization).subscribe(() => {
            this.toast.show('Localizations Updated');

            if (this.selectedLocalization.name === this.i18n.getActive().name) {
                this.i18n.setLocalization(this.selectedLocalization);
            }
        });
    }

    /**
     * Show modal for updating existing localization or creating new one.
     */
    public showCrupdateLocalizationModal(localization?: Localization) {
        this.modal.show(CrupdateLocalizationModalComponent, {localization}).onDone.subscribe(loc => {
            if (localization) {
                localization = loc;
            } else {
                this.localizations.push(loc);
                this.setSelectedLocalization(loc);
            }
        });
    }

    /**
     * Fetch currently selected localization
     * (if needed) including its translations.
     */
    public setSelectedLocalization(localization: Localization) {
        this.selectedLocalization = localization;
        this.translations = localization.lines;
        this.searchQuery.setValue(null);

        if (this.translations && Object.keys(this.translations).length || ! localization.name) return;

        this.localizationsApi.get(this.selectedLocalization.name).subscribe(loc => {
            this.selectedLocalization = loc;
            this.translations = loc.lines;
        });
    }

    /**
     * Ask user to confirm selected language deletion.
     */
    public confirmLocalizationDeletion(language: Localization) {
        this.modal.show(ConfirmModalComponent, {
            title: 'Delete Localization',
            body: 'Are you sure you want to delete this localization?',
            ok: 'Delete'
        }).onDone.subscribe(() => {
            this.deleteLocalization(language);
        });
    }

    /**
     * Delete currently selected language.
     */
    private deleteLocalization(language: Localization) {
        this.localizationsApi.delete(language.id).subscribe(() => {
            this.toast.show('Localization Deleted');
            this.localizations.splice(this.localizations.indexOf(language), 1);

            if (this.selectedLocalization === language) {
                this.setSelectedLocalization(this.localizations[0] || new Localization());
            }
        })
    }

    /**
     * Set specified localizations on component.
     */
    private setLocalizations(localizations: Localization[]) {
        this.localizations = localizations;

        this.localizations.forEach(localization => {
            if (localization.name === this.settings.get('i18n.default_localization')) {
                this.setSelectedLocalization(localization);
            }
        });
    }

    /**
     * Bind search query input.
     */
    private bindSearchQuery() {
        this.searchQuery
            .valueChanges
            .debounceTime(150)
            .distinctUntilChanged()
            .subscribe(query => {
                this.filterLocalizationLines(query);
            });
    }

    /**
     * Filter currently active language's translation
     * lines by specified search query.
     */
    private filterLocalizationLines(query: string) {
        //if there's no search query, restore original lines and bail
        if ( ! query) return this.translations = this.selectedLocalization.lines;

        let filtered = {};

        for (let key in this.selectedLocalization.lines as Object) {
            let value = this.selectedLocalization.lines[key];

            if (utils.strContains(key, query) || utils.strContains(value, query)) {
                filtered[key] = value;
            }
        }

        this.translations = filtered;
    }

    /**
     * Get specified object keys.
     */
    public objectKeys(object: Object): string[] {
        if ( ! object) return [];
        return Object.keys(object);
    }
}
