import {Artist} from "./models/Artist";
import {Album} from "./models/Album";
import {Track} from "./models/Track";
import {Playlist} from "./models/Playlist";
import {User} from "./models/User";

export interface SearchResponse {
    artists: Artist[],
    albums: Album[],
    tracks: Track[],
    playlists: Playlist[],
    users: User[],
}