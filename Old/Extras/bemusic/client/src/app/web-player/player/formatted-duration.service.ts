import {Injectable} from '@angular/core';

@Injectable()
export class FormattedDuration {

    public fromSeconds(seconds: number) {
        if ( ! seconds) return;

        let date = new Date(null);
        date.setSeconds(seconds);
        return date.toISOString().substr(15, 4);
    }

    public fromMilliseconds(ms: number) {
        if ( ! ms) return;

        let date = new Date(null);
        date.setMilliseconds(ms);
        let string = date.toISOString().substr(14, 5);

        //if time is "05:43" return "5:43"
        if (string[0] === '0') {
            return string.substr(1, 4);
        }

        return string;
    }

    public toVerboseString(ms: number): string {
        let date = new Date(ms);
        let str = '';

        const hours = date.getUTCHours();
        if (hours) str += hours + "hr ";

        const minutes = date.getUTCMinutes();
        if (minutes) str += minutes + "min ";

        const seconds = date.getUTCMinutes();
        if (seconds) str += seconds + "sec ";

        return str;
    }
}
