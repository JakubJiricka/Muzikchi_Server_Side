<?php namespace App\Http\Controllers;

use App;
use App\Track;
use App\Services\Paginator;
use Illuminate\Http\Request;
use App\Http\Requests\ModifyTracks;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Vebto\Bootstrap\Controller;
use App\Score;
use Auth;

class TrackController extends Controller {

	/**
	 * @var Track
	 */
	private $track;

    /**
     * @var Request
     */
    private $request;

    /**
     * TrackController constructor.
     *
     * @param Track $track
     * @param Request $request
     */
    public function __construct(Track $track, Request $request)
	{
		$this->track = $track;
        $this->request = $request;
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return LengthAwarePaginator
	 */
	public function index()
	{
        $this->authorize('index', Track::class);

        $params = $this->request->all();
        $params['order_by'] = isset($params['order_by']) ? $params['order_by'] : 'spotify_popularity';

	    return (new Paginator($this->track))->paginate($params);
	}

	/**
	 * Find track matching given id.
	 *
	 * @param  int  $id
	 * @return Track
	 */
	public function show($id)
	{
        $track = $this->track->with('album.artist', 'album.tracks')->findOrFail($id);

	    $this->authorize('show', $track);

	    return $track;
	}

    /**
     * Update existing track.
     *
     * @param int $id
     * @param ModifyTracks $validate
     * @return Track
     */
	public function update($id, ModifyTracks $validate)
	{
		$track = $this->track->findOrFail($id);

		$this->authorize('update', $track);

		$track->fill($this->request->except('album'))->save();

		return $track;
	}

    /**
     * Create a new track.
     *
     * @param ModifyTracks $validate
     * @return Track
     */
    public function store(ModifyTracks $validate)
    {
        $this->authorize('store', Track::class);

        $track = $this->track->create($this->request->all());

        return $track;
    }

	/**
	 * Remove tracks from database.
	 *
	 * @return mixed
	 */
	public function destroy()
	{
		$this->authorize('destroy', Track::class);

        $this->validate($this->request, [
            'ids'   => 'required|array',
            'ids.*' => 'required|integer'
        ]);

	    return $this->track->destroy($this->request->get('ids'));
	}

	/**
	 * Create a new track.
	 *
	 * @param ModifyTracks $validate
	 * @return Track
	 */
	public function trackscore()
	{
		//$this->authorize('store', Track::class);
		$user_id = $this->request->get('user_id');
		$track_id = $this->request->get('id');
		$score = $this->request->get('score');
//		$user_id = 1;
//		$track_id = 143283;
//		$score = 10;
		$trackscore = \DB::table('tot_score')
						->where('user_id',$user_id)
						->where('track_id',$track_id)
						->first();
		if(!empty($trackscore)) {
			$trackupdate = \DB::table('tot_score')
							->where('user_id',$user_id)
							->where('track_id',$track_id)
							->update(['score'=>$score]);
		}else {
			$trackcreate = Score::create(['user_id'=>$user_id, 'track_id'=>$track_id, 'score'=>$score]);
		}
		$track = \DB::table('tracks')->where('id',$track_id)->first();
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
		return \Response::json($track);
	}
}
