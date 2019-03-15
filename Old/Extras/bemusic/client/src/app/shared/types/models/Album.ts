import {Artist} from "./Artist";
import {Track} from "./Track";

export class Album {
	id: number;
	name: string;
	release_date?: string;
	image?: string;
	artist_id?: number;
	spotify_popularity?: boolean;
	fully_scraped: boolean;
	temp_id?: string;
	artist?: Artist;
	tracks?: Track[];
    views: number = 0;

	constructor(params: Object = {}) {
        for (let name in params) {
            this[name] = params[name];
        }
    }
}