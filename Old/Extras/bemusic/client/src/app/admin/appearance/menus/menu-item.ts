export class MenuItem {
    id: number;
    label: string;
    action: string;
    type: string = 'link';
    order: number = 1;
    condition: string = null;
    position: number = 0;

    constructor(params: Object = {}) {
        for (let name in params) {
            this[name] = params[name];
        }

        this.id = Math.floor(Math.random() * (1000 - 1));
    }
}