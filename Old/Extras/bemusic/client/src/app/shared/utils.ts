import {Injectable} from '@angular/core';
import {Settings} from "./settings.service";

declare let Symbol: any;

@Injectable()
export class utils {

    private loadedScripts = {};

    constructor(private settings: Settings) {}

    static isIterable(item) {
        return typeof item[Symbol.iterator] === 'function' || this.isFileList(item);
    }

    static isFileList(item) {
        return item instanceof FileList;
    }

    static strContains(haystack: string|string[], needle: string): boolean {
        if ( ! haystack || ! needle) return false;

        needle = needle.toLowerCase();

        if ( ! Array.isArray(haystack)) {
            haystack = [haystack];
        }

        for (let i = 0; i < haystack.length; i++) {
            if (haystack[i].toLowerCase().indexOf(needle) > -1) return true;
        }

        return false;
    }

    /**
     * Convert specified string to snake_case
     */
    static toSnakeCase(string: string) {
        return string
            .replace(/\s/g, '_')
            .replace(/\.?([A-Z]+)/g, function (x,y){return "_" + y})
            .replace(/^_/, '')
            .toLowerCase();
    }

    /**
     * Get object property via dot notation string.
     */
    static getObjectProp(obj: Object, prop: string): any {
        if ( ! obj) return null;
        const arr = prop.split('.');
        while(arr.length && (obj = obj[arr.shift()]));
        return obj;
    }

    /**
     * Uppercase first letter of specified string.
     */
    static ucFirst(string: string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    /**
     * Flatten specified array of arrays.
     */
    static flattenArray(arrays: any[][]): any[] {
        return [].concat.apply([], arrays);
    }

    /**
     * Slugify given string for use in urls.
     */
    static slugifyString(text): string {
        if ( ! text) return text;

        return text.trim()
            .replace(/["']/g, '')
            .replace(/[^a-z0-9-]/gi, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '')
            .toLowerCase();
    }

    static randomString() {
        return (Math.random() + 1).toString(36).substring(7);
    }

    /**
     * Load js script and return promise resolved on script load event.
     */
    public loadScript(url, params: {id?: string, force?: boolean} = {}): Promise<any> {
        //script is already loaded, return resolved promise
        if (this.loadedScripts[url] === 'loaded' && ! params.force) {
            return new Promise((resolve) => resolve());

            //script has never been loaded before, load it, return promise and resolve on script load event
        } else if ( ! this.loadedScripts[url]) {
            this.loadedScripts[url] = new Promise((resolve, reject) => {
                let s: HTMLScriptElement = document.createElement('script');
                s.async = true;
                s.id = params.id || url.split('/').pop();
                s.src = url.indexOf('//') > -1 ? url : this.settings.getBaseUrl(true)+url;

                s.onload = () => {
                    this.loadedScripts[url] = 'loaded';
                    resolve();
                };

                document.body.appendChild(s);
            });

            return this.loadedScripts[url];

            //script is still loading, return existing promise
        } else {
            return this.loadedScripts[url];
        }
    }
}