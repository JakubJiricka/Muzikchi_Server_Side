import {User} from "./User";
import {Track} from "./Track";

export class Playlist {
    id: number;
    name: string;
    public: number;
	image: string;
	description: string;
    created_at: string;
    updated_at: string;
    is_owner: string;
    editors?: User[];
    tracks?: Track[];
    views: number = 0;

    constructor(params: Object = {}) {
        for (let name in params) {
            this[name] = params[name];
        }
    }
}