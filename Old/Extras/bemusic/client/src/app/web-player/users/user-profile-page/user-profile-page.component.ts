import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {ActivatedRoute, NavigationEnd, Router} from "@angular/router";
import {User} from "../../../shared/types/models/User";
import {Settings} from "../../../shared/settings.service";
import {WebPlayerUrls} from "../../web-player-urls.service";
import {Subscription} from "rxjs/Subscription";
import {Users} from "../users.service";
import {CurrentUser} from "../../../auth/current-user";

@Component({
    selector: 'user-profile-page',
    templateUrl: './user-profile-page.component.html',
    styleUrls: ['./user-profile-page.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class UserProfilePageComponent implements OnInit {

    /**
     * Active component subscriptions.
     */
    private subscriptions: Subscription[] = [];

    /**
     * User model.
     */
    public user: User;

    /**
     * Currently active tab.
     */
    public activeTab = 'playlists';

    /**
     * UserProfilePageComponent Constructor.
     */
    constructor(
        private route: ActivatedRoute,
        private router: Router,
        private settings: Settings,
        public urls: WebPlayerUrls,
        private users: Users,
        public currentUser: CurrentUser
    ) {}

    ngOnInit() {
        this.setActiveTabFromUrl(this.router.url);
        this.bindToRoutingEvents();
    }

    ngOnDestroy() {
        this.subscriptions.forEach(subscription => {
            subscription.unsubscribe();
        });
        this.subscriptions = [];
    }

    /**
     * Get profile background image url.
     */
    public getProfileBackground() {
        return this.settings.getBaseUrl() + 'assets/images/default/artist-big.png'
    }

    /**
     * Check if active tab matches specified one.
     */
    public activeTabIs(name: string) {
        return this.activeTab === name;
    }

    /**
     * Follow specified user with currently logged in user.
     */
    public follow(user: User) {
        this.users.follow(user.id).subscribe(() => {
            this.currentUser.addFollower(user);
        });
    }

    /**
     * Unfollow specified user with currently logged in user.
     */
    public unfollow(user: User) {
        this.users.unfollow(user.id).subscribe(() => {
            this.currentUser.removeFollower(user);
        });
    }

    public currentUserIsFollowing(user: User) {
        return this.currentUser.isFollowing(user);
    }

    /**
     * Check if specified user is currently logged in.
     */
    public isCurrentUser(user: User) {
        return user.id === this.currentUser.get('id');
    }

    /**
     * Bind to router state change events.
     */
    private bindToRoutingEvents() {
        //set user model
        this.route.data.subscribe(data => {
            this.user = data.user;
        });

        //change active tab
        const sub = this.router.events
            .filter(event => event instanceof NavigationEnd)
            .subscribe((event: NavigationEnd) => {
                this.setActiveTabFromUrl(event.url);
            });

        this.subscriptions.push(sub);
    }

    /**
     * Set currently active tab based on specified url.
     */
    private setActiveTabFromUrl(url: string) {
        const tab = url.split('/').pop();

        switch (tab) {
            case 'following':
                this.activeTab = 'following';
                break;
            case 'followers':
                this.activeTab = 'followers';
                break;
            default:
                this.activeTab = 'playlists';
        }
    }
}
