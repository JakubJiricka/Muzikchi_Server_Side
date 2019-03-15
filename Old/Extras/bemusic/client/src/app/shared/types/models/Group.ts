import {User} from "./User";

export class Group {
	id: number;
	name: string;
	permissions?: string;
	default: boolean;
	guests: boolean;
	created_at?: string;
	updated_at?: string;
	users?: User[];

	constructor(params: Object = {}) {
        for (let name in params) {
            this[name] = params[name];
        }
    }
}