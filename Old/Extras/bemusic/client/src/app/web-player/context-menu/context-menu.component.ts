import {ElementRef, Injector, Input, OnInit, Renderer2} from '@angular/core';
import {Track} from "../../shared/types/models/Track";
import {Player} from "../player/player.service";
import {UserLibrary} from "../users/user-library/user-library.service";
import {WebPlayerUrls} from "../web-player-urls.service";
import {ContextMenu} from "./context-menu.service";
import {ToastService} from "../../shared/toast/toast.service";
import {ModalService} from "../../shared/modal/modal.service";
import * as copyToClipboard from 'copy-to-clipboard';
import {CurrentUser} from "../../auth/current-user";
import {Router} from "@angular/router";
import {ShareMediaItemModalComponent} from "./share-media-item-modal/share-media-item-modal.component";
import {Settings} from "../../shared/settings.service";
import {WebPlayerState} from "../web-player-state.service";

export abstract class ContextMenuComponent<T> {
    protected player: Player;
    protected library: UserLibrary;
    public urls: WebPlayerUrls;
    protected contextMenu: ContextMenu;
    protected toast: ToastService;
    protected modal: ModalService;
    protected renderer: Renderer2;
    protected el: ElementRef;
    public currentUser: CurrentUser;
    protected router: Router;
    public settings: Settings;
    protected state: WebPlayerState;

    protected activePanel = 'primary';

    @Input() public item: T;

    public params: {type: string};

    constructor(protected injector: Injector) {
        this.player = this.injector.get(Player);
        this.library = this.injector.get(UserLibrary);
        this.urls = this.injector.get(WebPlayerUrls);
        this.contextMenu = this.injector.get(ContextMenu);
        this.toast = this.injector.get(ToastService);
        this.modal = this.injector.get(ModalService);
        this.renderer = this.injector.get(Renderer2);
        this.el = this.injector.get(ElementRef);
        this.currentUser = this.injector.get(CurrentUser);
        this.router = this.injector.get(Router);
        this.settings = this.injector.get(Settings);
        this.state = this.injector.get(WebPlayerState);
    }

    /**
     * Get tracks that should be used by context menu.
     */
    public abstract getTracks(): Track[];

    /**
     * Add specified tracks to player queue.
     */
    public addToQueue() {
        this.player.queue.prepend(this.getTracks());
        this.contextMenu.close();
    }

    /**
     * Add specified tracks to user's library.
     */
    public saveToLibrary() {
        this.contextMenu.close();

        if ( ! this.currentUser.isLoggedIn()) {
            return this.router.navigate(['/login']);
        }

        this.library.add(this.getTracks());
    }

    /**
     * Copy fully qualified album url to clipboard.
     */
    public copyLinkToClipboard(method: string, item?: any) {
        let url = this.urls.routerLinkToUrl(this.urls[method](item || this.item));
        url = url.replace(/ /g, '+');
        copyToClipboard(url);
        this.contextMenu.close();
        this.toast.show('Copied link to clipboard.');
    }

    /**
     * Open share modal for current item.
     */
    public openShareModal() {
        this.contextMenu.close();
        this.modal.show(ShareMediaItemModalComponent, {mediaItem: this.item, type: this.params.type}).onDone.subscribe(() => {
            //send emails
        })
    }

    public openPanel(name: string) {
        this.activePanel = name;
    }

    public activePanelIs(name: string) {
        return this.activePanel === name;
    }
}
