import {Component, OnInit, ViewEncapsulation} from "@angular/core";
import {SettingsPanelComponent} from "../settings-panel.component";

@Component({
    selector: 'blocked-artists-settings',
    templateUrl: './blocked-artists-settings.component.html',
    styleUrls: ['./blocked-artists-settings.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class BlockedArtistsSettingsComponent extends SettingsPanelComponent implements OnInit {

    /**
     * Blocked artist input model.
     */
    public blockedArtist: string;

    /**
     * List of blocked artist names.
     */
    public blockedArtists: string[] = [];

    ngOnInit() {
        const blockedArtists = this.state.client['artists.blocked'];
        this.blockedArtists = blockedArtists ? JSON.parse(blockedArtists) : [];
    }

    /**
     * Add a new artist to blocked artists list.
     */
    public addBlockedArtist() {
        if ( ! this.blockedArtist) return;

        if (this.blockedArtists.findIndex(curr => curr === this.blockedArtist) > -1) {
            return this.blockedArtist = null;
        }

        this.blockedArtists.push(this.blockedArtist);
        this.blockedArtist = null;
    }

    /**
     * Remove specified artist from blocked artists list.
     */
    public removeBlockedArtist(blockedArtist: string) {
        let i = this.blockedArtists.findIndex(curr => curr === blockedArtist);
        this.blockedArtists.splice(i, 1);
    }

    /**
     * Save current settings to the server.
     */
    public saveSettings() {
        let payload = {'artists.blocked': JSON.stringify(this.blockedArtists)};
        this.settings.save({client: payload}).subscribe(() => {
            this.toast.show('Saved settings');
        });
    }
}
