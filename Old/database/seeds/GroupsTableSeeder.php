<?php

use App\Group;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class GroupsTableSeeder extends Seeder
{
    /**
     * @var Group
     */
    private $group;

    /**
     * @var User
     */
    private $user;

    /**
     * GroupsTableSeeder constructor.
     *
     * @param Group $group
     * @param User $user
     */
    public function __construct(Group $group, User $user)
    {
        $this->user = $user;
        $this->group = $group;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ( ! $this->group->where('name', 'guests')->orWhere('guests', 1)->first()) {
            $this->group->create(['name' => 'guests', 'permissions' => json_encode($this->getGuestPermissions()), 'guests' => 1]);
        }

        if ( ! $users = $this->group->where('name', 'users')->orWhere('default', 1)->first()) {
            $users = $this->group->create(['name' => 'users', 'permissions' => json_encode($this->getUserPermissions()), 'default' => 1]);
        }

        if ( ! $this->group->where('name', 'editors')->first()) {
            $this->group->create([
                'name' => 'editors',
                'permissions' => json_encode(
                    array_merge($this->getUserPermissions(), $this->getEditorPermissions())
                )
            ]);
        }

        $this->attachUsersGroupToExistingUsers($users);
    }

    /**
     * Attach default user's group to all existing users.
     *
     * @param Group $group
     */
    private function attachUsersGroupToExistingUsers(Group $group)
    {
        $this->user->with('groups')->orderBy('id', 'desc')->select('id')->chunk(500, function(Collection $users) use($group) {
            $insert = $users->filter(function(User $user) use ($group) {
                return ! $user->groups->contains('id', $group->id);
            })->map(function(User $user) use($group) {
                return ['user_id' => $user->id, 'group_id' => $group->id, 'created_at' => Carbon::now()];
            })->toArray();

            DB::table('user_group')->insert($insert);
        });
    }

    /**
     * Get default permissions for regular users group.
     *
     * @return array
     */
    private function getUserPermissions()
    {
        return [
            'artists.view' => 1,
            'albums.view' => 1,
            'tracks.view' => 1,
            'genres.view' => 1,
            'lyrics.view' => 1,
            'users.view'  => 1,
            'playlists.create' => 1,
            'localizations.show' => 1,
            'pages.view' => 1,
            'uploads.create' => 1,
        ];
    }

    /**
     * Get default permissions for guests group.
     *
     * @return array
     */
    private function getGuestPermissions()
    {
        return [
            'artists.view' => 1,
            'albums.view' => 1,
            'tracks.view' => 1,
            'genres.view' => 1,
            'lyrics.view' => 1,
            'playlists.create' => 1,
            'users.view'  => 1,
            'pages.view' => 1,
        ];
    }

    /**
     * Get default permissions for editors group.
     *
     * @return array
     */
    private function getEditorPermissions()
    {
        return [
            'artists.create' => 1,
            'artists.update' => 1,
            'artists.delete' => 1,
            'albums.create' => 1,
            'albums.update' => 1,
            'albums.delete' => 1,
            'tracks.create' => 1,
            'tracks.update' => 1,
            'tracks.delete' => 1,
            'genres.create' => 1,
            'genres.update' => 1,
            'genres.delete' => 1,
        ];
    }
}
