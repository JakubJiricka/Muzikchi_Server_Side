<?php namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GroupPolicy
{
    use HandlesAuthorization;

    public function index(User $user)
    {
        return $user->hasPermission('groups.view');
    }

    public function store(User $user)
    {
        return $user->hasPermission('groups.create');
    }

    public function update(User $user)
    {
        return $user->hasPermission('groups.update');
    }

    public function destroy(User $user)
    {
        return $user->hasPermission('groups.delete');
    }
}
