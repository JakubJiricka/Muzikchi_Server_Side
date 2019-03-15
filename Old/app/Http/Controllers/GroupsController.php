<?php namespace App\Http\Controllers;

use App\User;
use App\Group;
use Illuminate\Http\Request;

class GroupsController extends Controller
{
    /**
     * User model.
     *
     * @var User
     */
    private $user;

    /**
     * Group model.
     *
     * @var Group
     */
    private $group;

    /**
     * Laravel request instance.
     *
     * @var Request
     */
    private $request;

    /**
     * GroupsController constructor.
     *
     * @param Request $request
     * @param Group $group
     * @param User $user
     */
    public function __construct(Request $request, Group $group, User $user)
    {
        $this->group   = $group;
        $this->user    = $user;
        $this->request = $request;
    }

    /**
     * Paginate all existing groups.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index()
    {
        $this->authorize('index', Group::class);

        $data = $this->group->paginate(13);

        return $data;
    }

    /**
     * Create a new group.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        $this->authorize('store', Group::class);

        $this->validate($this->request, [
            'name'        => 'required|unique:groups|min:2|max:255',
            'default'     => 'boolean',
            'guests'      => 'boolean',
            'permissions' => 'array'
        ]);

        $group = $this->group->forceCreate([
            'name'        => $this->request->get('name'),
            'permissions' => $this->request->get('permissions'),
            'default'     => $this->request->get('default', 0)
        ]);

        return $this->success(['data' => $group], 201);
    }

    /**
     * Update existing group.
     *
     * @param integer $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id)
    {
        $this->authorize('update', Group::class);

        $this->validate($this->request, [
            'name'        => "min:2|max:255|unique:groups,name,$id",
            'default'     => 'boolean',
            'guests'      => 'boolean',
            'permissions' => 'array'
        ]);

        $group = $this->group->findOrFail($id);

        $group->fill($this->request->all())->save();

        return $this->success(['data' => $group]);
    }

    /**
     * Delete group matching given id.
     *
     * @param integer $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $this->authorize('destroy', Group::class);

        $group = $this->group->findOrFail($id);

        $group->users()->detach();
        $group->delete();

        return $this->success([], 204);
    }

    /**
     * Add given users to group.
     *
     * @param integer $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function addUsers($groupId)
    {
        $this->authorize('update', Group::class);

        $this->validate($this->request, [
            'emails'   => 'required|array|min:1|max:25',
            'emails.*' => 'required|email|max:255'
        ], [
            'emails.*.email'   => 'Email address must be valid.',
            'emails.*.required' => 'Email address is required.',
        ]);

        $group = $this->group->findOrFail($groupId);

        $users = $this->user->with('groups')->whereIn('email', $this->request->get('emails'))->get(['email', 'id']);

        if ($users->isEmpty()) {
            return $this->error(null, 422);
        }

        //filter out users that are already attached to this group
        $users = $users->filter(function($user) use($groupId) {
            return ! $user->groups->contains('id', (int) $groupId);
        });

        $group->users()->attach($users->pluck('id')->toArray());

        return $this->success(['data' => $users]);
    }

    /**
     * Remove given users from group.
     *
     * @param integer $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeUsers($groupId)
    {
        $this->authorize('update', Group::class);

        $this->validate($this->request, [
            'ids'   => 'required|array|min:1',
            'ids.*' => 'required|integer'
        ]);

        $group = $this->group->findOrFail($groupId);

        $group->users()->detach($this->request->get('ids'));

        return $this->success(['data' => $this->request->get('ids')]);
    }
}
