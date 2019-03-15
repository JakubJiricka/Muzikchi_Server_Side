import {Injectable} from '@angular/core';
import {ToastComponent} from "./toast.component";
import {Translations} from "../translations/translations.service";
import {Settings} from "../settings.service";

@Injectable()
export class ToastService {

    /**
     * Toast component instance.
     */
    private componentInstance: ToastComponent;

    /**
     * ToastService Constructor.
     */
    constructor(private settings: Settings, private i18n: Translations) {}

    /**
     * Show toast.
     */
    public show(message: string, config: {delay?: number, type?: string, actions?: any} = {}) {
        if ( ! config.delay && config.delay !== 0) {
            config.delay = this.settings.get('toast.default_timeout', 4000);
        }

        this.componentInstance.message = this.i18n.t(message);

        return this.componentInstance.show(config.delay, config.type || 'default');
    }

    /**
     * Register toast component instance.
     */
    public registerComponentInstance(toast: ToastComponent) {
        this.componentInstance = toast;
    }
}