import {User} from "./User";
import {Album} from "./Album";
import {Playlist} from "./Playlist";
import {Lyric} from "./Lyric";

export class Track {
	id: number;
	name: string;
	album_name: string;
	number: number;
	duration?: number;
	artists?: string[];
	youtube_id?: string;
	spotify_popularity: number;
	album_id: number;
	temp_id?: string;
	url?: string;
	users?: User[];
	lyric?: Lyric;
	plays: number = 0;
	album?: Album;
	playlists?: Playlist[];

	constructor(params: Object = {}) {
        for (let name in params) {
            this[name] = params[name];
        }
    }
}