<?php namespace App\Http\Controllers;

use App;
use App\Album;
use App\Jobs\IncrementModelViews;
use Illuminate\Http\Request;
use App\Http\Requests\ModifyAlbums;
use App\Services\Albums\AlbumsRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Vebto\Bootstrap\Controller;
use Auth;

class AlbumController extends Controller {

    /**
     * @var Request
     */
    private $request;

    /**
     * @var AlbumsRepository
     */
    private $repository;

    /**
     * Create new AlbumController instance.
     *
     * @param AlbumsRepository $repository
     * @param Request $request
     */
	public function __construct(AlbumsRepository $repository, Request $request)
	{
        $this->request = $request;
        $this->repository = $repository;
    }

	/**
	 * Paginate all albums.
	 *
	 * @return LengthAwarePaginator
	 */
	public function index()
	{
		$this->authorize('index', Album::class);

	    return $this->repository->paginate($this->request->all());
	}

    /**
     * Get album matching specified ID.
     *
     * @param number $id
     * @return Album
     */
    public function show($id)
    {
        $this->authorize('show', Album::class);
		$track_image = ' ';
        $album = $this->repository->load($id);
		///
		$tracks = $album->tracks;
		$count = 0;
		foreach ($tracks as $track) {
			if($track_image == '' || $track_image == 0 || empty($track_image) ) $track_image =  $track->track_image;
			$track_id = $track->id;
			$score_inform = \DB::table('tot_score')->where('track_id', $track_id)
				->select(\DB::raw("sum(score) as score"))->first();
			if (!empty($score_inform)) {
				$score_mark = $score_inform->score;
			} else {
				$score_mark = 0;
			}
			if($score_mark == null) $score_mark = 0;
			$track->score = $score_mark;
			$score_high_inform = \DB::table('tot_score')
				->select(\DB::raw('SUM(score) as sumscore, track_id, user_id'))
				->groupBy('track_id')->orderBy('sumscore','desc')->first();
			if (!empty($score_high_inform)) {
				$score_high_mark = $score_high_inform->sumscore;
				$score_high_track_id = $score_high_inform->track_id;
				$score_high_user_id = $score_high_inform->user_id;;
			} else {
				$score_high_mark = 0;
				$score_high_track_id = 0;
				$score_high_user_id = 0;
			}
			$track->score_high = $score_high_mark;
			$track->score_high_track_id = $score_high_track_id;
			$track->score_high_user_id = $score_high_user_id;
			$user_score = 1 ;
			if (Auth::check()) {
				$user_id= Auth::user()->id;
				$score_user_inform = \DB::table('tot_score')
					->where('track_id', $track_id)
					->where('user_id', $user_id)
					->first();
				if(!empty($score_user_inform)) $user_score = $score_user_inform->score;
			}
			$track->user_score = $user_score;
			$tracks[$count] = $track;
			$count++;
		}
		$album->image = $track_image;
		$album->tracks = $tracks;
		///
        dispatch(new IncrementModelViews($album->id, 'album'));

        return $album;
    }

    /**
     * Update existing album.
     *
     * @param  int $id
     * @param ModifyAlbums $validate
     * @return Album
     */
	public function update($id, ModifyAlbums $validate)
	{
		$this->authorize('update', Album::class);

	    return $this->repository->update($id, $this->request->all());
	}

    /**
     * Create a new album.
     *
     * @param ModifyAlbums $validate
     * @return Album
     */
    public function store(ModifyAlbums $validate)
    {
        $this->authorize('store', Album::class);

        return $this->repository->create($this->request->all());
    }

	/**
	 * Remove specified albums.
	 *
	 * @return mixed
	 */
	public function destroy()
	{
	    $this->authorize('destroy', Album::class);

        $this->validate($this->request, [
            'ids'   => 'required|array',
            'ids.*' => 'required|integer'
        ]);

	    $this->repository->delete($this->request->get('ids'));

	    return $this->success();
	}
}
