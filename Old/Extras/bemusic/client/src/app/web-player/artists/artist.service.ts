import {Injectable} from '@angular/core';
import {Artists} from "./artists.service";
import {Album} from "../../shared/types/models/Album";
import {Player} from "../player/player.service";
import {Track} from "../../shared/types/models/Track";
import {WpUtils} from "../web-player-utils";
import {MediaItem} from "../media-item.service";
import {Artist} from "../../shared/types/models/Artist";

@Injectable()
export class ArtistService extends MediaItem<Artist> {

    /**
     * Top artist tracks.
     */
    private topTracks: Track[] = [];

    /**
     * ArtistService Constructor.
     */
    constructor(protected artists: Artists, protected player: Player) {
        super(player);
    }

    /**
     * Get artist top tracks.
     */
    public getTopTracks(count = 5) {
        return this.topTracks.slice(0, count);
    }

    /**
     * Get similar artists.
     */
    public getSimilar(count: number = 20) {
        if ( ! this.item.similar) return [];
        return this.item.similar.slice(0, count);
    }

    /**
     * Check if artist has any similar artists.
     */
    public hasSimilar() {
        return this.item.similar.length;
    }

    /**
     * Get loaded albums of artist.
     */
    public getAlbums() {
        return this.paginatedData;
    }

    /**
     * Get artist biography and images.
     */
    public getBio(): { images: { url: string }[], bio: string } {
        return this.item.bio as any;
    }

    /**
     * Destroy artist service.
     */
    public destroy() {
        this.topTracks = [];
        super.destroy();
    }

    /**
     * Load next infinite load albums page.
     */
    protected loadNextPage() {
        return this.artists.paginateArtistAlbums(this.item.id, this.pagination.current_page + 1).map(response => {
            response.data = this.addArtistToAlbums(response.data);
            return response;
        });
    }

    /**
     * Get tracks from specified albums.
     */
    public getTracks(paginatedData?: any[]): Track[] {
        let data = (paginatedData ? paginatedData : this.paginatedData), tracks = [];

        data.forEach(album => {
            tracks = tracks.concat(WpUtils.assignAlbumToTracks(album.tracks, album));
        });

        //if paginated tracks were not specified, it means initial
        //data is being requested, so we should prepend top tracks to it
        if ( ! paginatedData) {
            tracks = this.topTracks.concat(tracks);
        }

        return tracks;
    }

    /**
     * Load artist from backend.
     */
    protected async loadItem(id: number, params = {}) {
        const response = await this.artists.get(id, params).toPromise();
        response.albums.data = this.addArtistToAlbums(response.albums.data, response.artist);
        this.setItem(response.artist, response.albums);
        this.topTracks = response.top_tracks || [];
    }

    /**
     * Add simplified artist object to all albums, for generating urls and context menus.
     */
    private addArtistToAlbums(albums: Album[], artist?: Artist) {
        if ( ! artist) artist = this.item;

        return albums.map(album => {
            album.artist = {name: artist.name, id: artist.id} as Artist;
            return album;
        });
    }
}
