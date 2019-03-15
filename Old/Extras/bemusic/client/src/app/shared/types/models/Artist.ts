import {Album} from "./Album";
import {Genre} from "./Genre";

export class Artist {
	id: number;
	name: string;
	spotify_followers?: number;
	spotify_popularity?: boolean;
	image_small?: string;
	image_large?: string;
	fully_scraped?: boolean = true;
	updated_at?: string;
	bio?: string;
	albums?: Album[];
	similar?: Artist[];
	genres?: Genre[];
	views: number = 0;

	constructor(params: Object = {}) {
        for (let name in params) {
            this[name] = params[name];
        }
    }
}