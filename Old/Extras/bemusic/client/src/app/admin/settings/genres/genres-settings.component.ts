import {Component, OnInit, ViewEncapsulation} from "@angular/core";
import {SettingsPanelComponent} from "../settings-panel.component";

@Component({
    selector: 'genres-settings',
    templateUrl: './genres-settings.component.html',
    styleUrls: ['./genres-settings.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class GenresSettingsComponent extends SettingsPanelComponent implements OnInit {

    /**
     * Genre input model.
     */
    public genre: string;

    /**
     * Homepage genres.
     */
    public genres: string[] = [];

    ngOnInit() {
        const genres = this.state.client['homepage.genres'];
        this.genres = genres ? JSON.parse(genres) : [];
    }

    /**
     * Add a new genre to homepage genres.
     */
    public addGenre() {
        if ( ! this.genre) return;

        if (this.genres.findIndex(curr => curr === this.genre) > -1) {
            return this.genre = null;
        }

        this.genres.push(this.genre);
        this.genre = null;
    }

    /**
     * Remove specified genre from homepage genres.
     */
    public removeGenre(genre: string) {
        let i = this.genres.findIndex(curr => curr === genre);
        this.genres.splice(i, 1);
    }

    /**
     * Save current settings to the server.
     */
    public saveSettings() {
        let payload = {'homepage.genres': JSON.stringify(this.genres)};
        this.settings.save({client: payload}).subscribe(() => {
            this.toast.show('Saved settings');


        });
    }
}
