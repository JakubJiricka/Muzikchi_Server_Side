<?php namespace App\Services;

use App\User;
use App\Group;
use App\Localization;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BootstrapData
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Localization
     */
    private $localization;

    /**
     * @var Group
     */
    private $groups;

    /**
     * BootstrapData constructor.
     *
     * @param Settings $settings
     * @param Request $request
     * @param Localization $localization
     * @param Group $groups
     */
    public function __construct(
        Settings $settings,
        Request $request,
        Localization $localization,
        Group $groups
    )
    {
        $this->groups = $groups;
        $this->request = $request;
        $this->settings = $settings;
        $this->localization = $localization;
    }

    /**
     * Get data needed to bootstrap the application.
     *
     * @return string
     */
    public function get()
    {
        $bootstrap = [];
        $bootstrap['settings'] = $this->settings->all();
        $bootstrap['settings']['base_url'] = url('');
        $bootstrap['settings']['version'] = config('site.version');
        $bootstrap['csrf_token'] = csrf_token();
        $bootstrap['guests_group'] = $this->groups->where('guests', 1)->first();
        $bootstrap['i18n'] = $this->getLocalizationsData() ?: null;
        $bootstrap['user'] = $this->getCurrentUser();

        if ($bootstrap['user']) {
            $bootstrap['tracks'] = $this->getUserTracks();
            $bootstrap['playlists'] = $this->getUserPlaylists();
            $bootstrap['user'] = $this->loadUserFollowedUsers($bootstrap['user']);
            $bootstrap['user'] = $bootstrap['user']->toArray();
        }

        return base64_encode(json_encode($bootstrap));
    }

    /**
     * Load current user and his groups.
     */
    private function getCurrentUser()
    {
        $user = $this->request->user();

        if ($user && ! $user->relationLoaded('groups')) {
            $user->load('groups');
        }

        return $user;
    }

    /**
     * Load users that current user is following.
     *
     * @param User $user
     * @return User
     */
    private function loadUserFollowedUsers(User $user)
    {
        return $user->load(['followedUsers' => function(BelongsToMany $q) {
            return $q->select('users.id', 'users.avatar');
        }]);
    }

    /**
     * Get ids of all tracks in current user's library.
     *
     * @return array
     */
    private function getUserTracks()
    {
        return $this->request->user()->tracks()->pluck('tracks.id')->toArray();
    }

    /**
     * Get ids of all tracks in current user's library.
     *
     * @return array
     */
    private function getUserPlaylists()
    {
        return $this->request->user()
            ->playlists()
            ->with(['editors' => function(BelongsToMany $q) {
                return $q->compact();
            }])
            ->select('playlists.id', 'playlists.name')
            ->get()
            ->toArray();
    }

    /**
     * Get currently selected i18n language.
     *
     * @return Localization
     */
    private function getLocalizationsData()
    {
        if ( ! $this->settings->get('i18n.enable')) return null;

        //get user selected language
        $userLang = $this->request->user() ? $this->request->user()->language : null;

        //get default language if user has not selected any languages
        if ( ! $userLang) {
            $userLang = $this->settings->get('i18n.default_localization');
        }

        if ($userLang) {
            return $this->localization->where('name', $userLang)->first();
        }
    }
}
