import {Social} from "./Social";
import {Track} from "./Track";
import {Playlist} from "./Playlist";
import {SocialProfile} from "./SocialProfile";
import {Group} from "./Group";

export class User {
	id: number;
	username?: string;
	first_name?: string;
	last_name?: string;
	avatar?: string;
	gender?: string;
	permissions?: string;
	email: string;
	password: string;
	language: string;
	timezone: string;
	country: string;
	remember_token?: string;
	created_at: string = '0000-00-00 00:00:00';
	updated_at: string = '0000-00-00 00:00:00';
	stripe_active: boolean;
	stripe_id?: string;
	stripe_subscription?: string;
	stripe_plan?: string;
	last_four?: string;
	trial_ends_at?: string;
	subscription_ends_at?: string;
	confirmed: boolean = true;
	groups: Group[] = [];
	confirmation_code?: string;
	isAdmin: string;
	social_profiles: SocialProfile[];
	has_password: boolean;
	followers_count: string;
	followed_users?: User[];
	followers?: User[];
	oauth?: Social[];
	tracks?: Track[];
	playlists?: Playlist[];

	constructor(params: Object = {}) {
        for (let name in params) {
            this[name] = params[name];
        }
    }
}