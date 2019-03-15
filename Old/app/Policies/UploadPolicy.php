<?php namespace App\Policies;

use App\User;
use App\Upload;
use Illuminate\Auth\Access\HandlesAuthorization;

class UploadPolicy
{
    use HandlesAuthorization;

    public function index(User $user)
    {
        return $user->hasPermission('uploads.view');
    }

    public function show(User $user, Upload $upload)
    {
        return $user->hasPermission('uploads.view') || $user->id === $upload->user_id;
    }

    public function store(User $user)
    {
        return $user->hasPermission('uploads.create');
    }

    public function destroy(User $user)
    {
        return $user->hasPermission('uploads.delete');
    }
}
