import {User} from "./User";

export class Upload {
    id: number;
    name: string;
    file_name: string;
    file_size: string;
    mime: string;
    extension: string;
    user_id: string;
    url?: string;
    thumbnail_url?: string;
    created_at?: string;
    updated_at?: string;
    path: string;
    formatted_size: string;
    user?: User;

    constructor(params: Object = {}) {
        for (let name in params) {
            this[name] = params[name];
        }
    }
}