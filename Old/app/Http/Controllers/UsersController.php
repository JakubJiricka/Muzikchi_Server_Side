<?php namespace App\Http\Controllers;

use App\Http\Requests\ModifyUsers;
use App\Playlist;
use App\Services\Auth\UserRepository;
use Auth;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\User;
use Illuminate\Http\Request;

class UsersController extends Controller {

    /**
     * @var User
     */
    private $model;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var Request
     */
    private $request;

    /**
     * UsersController constructor.
     *
     * @param User $user
     * @param UserRepository $userRepository
     * @param Request $request
     */
    public function __construct(User $user, UserRepository $userRepository, Request $request)
    {
        $this->model = $user;
        $this->request = $request;
        $this->userRepository = $userRepository;

        $this->middleware('auth', ['except' => ['show']]);
    }

    /**
     * Return a collection of all registered users.
     *
     * @return LengthAwarePaginator
     */
    public function index()
    {
        $this->authorize('index', User::class);

        return $this->userRepository->paginateUsers($this->request->all());
    }

    /**
     * Return user matching given id.
     *
     * @param integer $id
     * @return User
     */
    public function show($id)
    {
        $user = $this->model->with(['groups', 'social_profiles', 'followedUsers', 'followers', 'playlists' => function($q) {
            $q->with(['tracks.album' => function($query) {
                return $query->limit(1);
            }])->where('public', 1)->whereHas('tracks');
        }])->findOrFail($id);

        $user->playlists = $this->setPlaylistImage($user->playlists);

        $this->authorize('show', $user);

        return $user;
    }

    /**
     * Create a new user.
     *
     * @param ModifyUsers $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ModifyUsers $request)
    {
        $this->authorize('store', User::class);

        $user = $this->userRepository->create($this->request->all());

        return $this->success(['data' => $user], 201);
    }

    /**
     * Update an existing user.
     *
     * @param integer $id
     * @param ModifyUsers $request
     *
     * @return User
     */
    public function update($id, ModifyUsers $request)
    {
        $user = $this->userRepository->findOrFail($id);

        $this->authorize('update', $user);

        $user = $this->userRepository->update($user, $this->request->all());

        return $user;
    }

    /**
     * Delete multiple users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteMultiple()
    {
        $this->authorize('destroy', User::class);

        $this->validate($this->request, [
            'ids' => 'required|array|min:1'
        ]);

        $this->userRepository->deleteMultiple($this->request->get('ids'));

        return $this->success([], 204);
    }

    /**
     * Follow user with given id.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function follow($id)
    {
        $user = $this->model->findOrFail($id);

        if ($user->id !== Auth::user()->id) {
            Auth::user()->followedUsers()->sync([$id], false);
        }

        return $this->success();
    }

    /**
     * UnFollow user with given id.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function unfollow($id)
    {
        $user = $this->model->findOrFail($id);

        if ($user->id != Auth::user()->id) {
            Auth::user()->followedUsers()->detach($id);
        }

        return $this->success();
    }

    /**
     * Make sure all playlists have an image.
     *
     * @param Collection $playlists
     * @return Collection
     */
    private function setPlaylistImage($playlists)
    {
        return $playlists->map(function(Playlist $playlist) {
            if ( ! $playlist->getAttribute('image') && isset($playlist->tracks->first()->album->image)) {
                $playlist->image = $playlist->tracks->first()->album->image;
            }

            if ( ! $playlist->image) {
                $playlist->image = url('assets/images/default/artist_small.jpg');
            }

            unset($playlist->tracks);

            return $playlist;
        });
    }
}
