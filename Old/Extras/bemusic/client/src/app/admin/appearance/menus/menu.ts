import {MenuItem} from "./menu-item";

export class Menu {
    name: string;
    position: string = 'header';
    items: MenuItem[] = [];

    constructor(params: Object = {}) {
        for (let name in params) {
            this[name] = params[name];
        }
    }
}