import {Injectable} from '@angular/core';
import {Track} from "../../shared/types/models/Track";
import {PlayerState} from "./player-state.service";
import {shuffler} from "./shuffler";
import {LocalStorage} from "../../shared/local-storage.service";

@Injectable()
export class PlayerQueue {

    /**
     * Queue after shuffling.
     */
    private queue: Track[] = [];

    /**
     * Original queue before shuffling.
     */
    private originalQueue: Track[] = [];

    /**
     * Current queue pointer.
     */
    private pointer: number = 0;

    /**
     * Id of item that is currently queued, if any.
     * (album, artist, playlist etc)
     */
    private queuedItemId: number|string;

    /**
     * PlayerState Service.
     */
    constructor(private state: PlayerState, private localStorage: LocalStorage) {}

    /**
     * Get all queue items.
     */
    public getAll(): Track[] {
        return this.queue;
    }

    /**
     * Check if specified track is currently in queue.
     */
    public has(track: Track): boolean {
        return this.queue.findIndex(predicate => {
            return predicate.id === track.id
        }) >= 0;
    }

    /**
     * Move queue pointer to specified track.
     */
    public select(track: Track) {
        if ( ! track) track = new Track();

        this.set(this.queue.findIndex(predicate => {
            return predicate.id === track.id;
        }));
    }

    /**
     * Check if queue is empty.
     */
    public empty() {
        return this.getTotal() > 0;
    }

    /**
     * Override current queue with specified items.
     */
    public override(params: {tracks: Track[], queuedItemId?: number|string}, queuePointer: number = 0) {
        this.queuedItemId = params.queuedItemId;
        this.queue = params.tracks.slice();
        if (this.state.shuffling) this.shuffle(true);
        this.originalQueue = params.tracks.slice();
        this.set(queuePointer);
    }

    public append(tracks: Track[]) {
        this.queue = this.queue.concat(tracks.slice());
        this.originalQueue = this.queue.concat(tracks.slice());
    }

    public prepend(tracks: Track[]) {
        this.queue = this.addTracksAtIndex(this.queue, tracks);
        this.originalQueue = this.addTracksAtIndex(this.originalQueue, tracks);
    }

    private addTracksAtIndex(array: Track[], tracksToAdd: Track[]) {
        const tail = array.splice(this.pointer + 1);
        return [...array, ...tracksToAdd, ...tail];
    }

    public remove(track: Track) {
        let i = this.queue.findIndex(predicate => predicate === track);
        if (i === -1) i = this.queue.findIndex(predicate => predicate.id === track.id);
        this.queue.splice(i, 1);
    }

    /**
     * Shuffle the queue.
     */
    public shuffle(keepFirst = false) {
        this.queue = shuffler.shuffle(this.getAll(), keepFirst);
    }

    public getQueuedItem() {
        return this.queuedItemId;
    }

    public setQueuedItem(id: number|string) {
        this.queuedItemId = id;
    }

    public itemIsQueued(itemId: number|string) {
        return this.queuedItemId === itemId;
    }

    /**
     * Restore queue to original (if it has been shuffled)
     */
    public restoreOriginal() {
        this.queue = this.originalQueue.slice();
    }

    public getFirst(): Track {
        return this.get(0);
    }

    public getLast(): Track {
        return this.get(this.getTotal() - 1);
    }

    public getTotal() {
        return this.queue.length;
    }

    public getNext(): Track {
        return this.get(this.pointer + 1);
    }

    /**
     * Check if current track is the last one in queue.
     */
    public isLast() {
        return this.getLast() === this.getCurrent();
    }

    /**
     * Check if current track is the first one in queue.
     */
    public isFirst() {
        return this.getFirst() === this.getCurrent();
    }

    public getPrevious(): Track {
        return this.get(this.pointer - 1);
    }

    public getCurrent() {
        return this.get(this.pointer);
    }

    public get(index: number): Track {
        return this.queue[index] || null;
    }

    public set(index: number) {
        if (index === -1) index = null;
        this.pointer = index;
        this.localStorage.set('player.queue.pointer', index);
    }
}
