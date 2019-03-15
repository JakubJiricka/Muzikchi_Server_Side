import {DefaultUrlSerializer, UrlTree} from "@angular/router";

export class CustomUrlSerializer extends DefaultUrlSerializer {
    parse(url: string): UrlTree {
        url = url.replace(/\+/g, '%20');
        url = url.replace(/\(/g, '%28').replace(/\)/g, '%29');
        return super.parse(url);
    }

    serialize(tree: UrlTree): string {
        return super.serialize(tree)
            .replace(/%20/g, '+')
            .replace(/%28/g, '(')
            .replace(/%29/g, ')');
    }
}