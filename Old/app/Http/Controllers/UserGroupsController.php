<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Auth\UserRepository;

class UserGroupsController extends Controller
{
    /**
     * UserRepository instance.
     *
     * @var UserRepository
     */
    private $repository;

    /**
     * Laravel request instance.
     *
     * @var Request
     */
    private $request;

    /**
     * UserGroupsController constructor.
     *
     * @param UserRepository $repository
     * @param Request $request
     */
    public function __construct(UserRepository $repository, Request $request)
    {
        $this->repository = $repository;
        $this->request = $request;
    }

    /**
     * Attach specified groups to user.
     *
     * @param int $userId
     * @return int
     */
    public function attach($userId)
    {
        $user = $this->repository->findOrFail($userId);

        $this->authorize('update', $user);

        return $this->repository->attachGroups($user, $this->request->get('groups'), 'attach');
    }

    /**
     * Detach specified groups from user.
     *
     * @param int $userId
     * @return int
     */
    public function detach($userId)
    {
        $user = $this->repository->findOrFail($userId);

        $this->authorize('update', $user);

        return $this->repository->detachGroups($user, $this->request->get('groups'));
    }
}
