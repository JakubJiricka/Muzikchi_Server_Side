import {Component, ViewEncapsulation} from "@angular/core";
import {SettingsPanelComponent} from "../settings-panel.component";

@Component({
    selector: 'providers-settings',
    templateUrl: './providers-settings.component.html',
    encapsulation: ViewEncapsulation.None,
})
export class ProvidersSettingsComponent extends SettingsPanelComponent {

    private providers = ['artist', 'album', 'search', 'genres', 'new_releases', 'top_50', 'biography'];

    /**
     * Check if specified provider API keys should be requested in settings page.
     */
    public needProviderKeys(name: string): boolean {
        return this.providers.findIndex(provider => {
            let currentName = this.state.client[provider+'_provider'] || '';
            return currentName.toLowerCase() === name.toLowerCase();
        }) > -1;
    }

    /**
     * Generate sitemap for the site.
     */
    public generateSitemap() {
        this.loading = true;

        this.http.post('admin/sitemap/generate').subscribe(() => {
            this.loading = false;
            this.toast.show('Sitemap generated.');
        }, () => {
            this.loading = false;
        });
    }
}
