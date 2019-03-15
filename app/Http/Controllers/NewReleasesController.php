<?php namespace App\Http\Controllers;

use App;
use App\Album;
use App\Services\Providers\ProviderResolver;
use Cache;
use Carbon\Carbon;
use Vebto\Bootstrap\Controller;
use Vebto\Settings\Settings;
use Auth;

class NewReleasesController extends Controller
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var ProviderResolver
     */
    private $resolver;

    /**
     * PopularAlbumsController constructor.
     *
     * @param Settings $settings
     * @param ProviderResolver $resolver
     */
    public function __construct(Settings $settings, ProviderResolver $resolver)
    {
        $this->settings = $settings;
        $this->resolver = $resolver;
    }

    /**
     * Get most popular albums.
     *
     * @return mixed
     */
    public function index()
    {
        $this->authorize('index', Album::class);

        return Cache::remember('albums.latest', $this->getCacheTime(), function() {
            $albums = $this->resolver->get('new_releases')->getNewReleases();
            ////
            $album_count = 0;
            foreach ($albums as $album) {
                $tracks = $album->tracks;
                $track_image = ' ';
                $count = 0;
                    foreach ($tracks as $track) {
                        $track_id = $track->id;
                        if($track_image == '' || $track_image == 0 || empty($track_image) ) $track_image =  $track->track_image;
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
                $albums[$album_count]->track_image = $track_image;
                //$albums[$album_count]->image  = $track_image;
                $albums[$album_count]->tracks = $tracks;
                $album_count++;
            }
            ///
            return ! empty($albums) ? $albums : null;
        });
    }

    /**
     * Get time popular albums should be cached for.
     *
     * @return Carbon
     */
    private function getCacheTime()
    {
        return Carbon::now()->addDays($this->settings->get('cache.homepage_days'));
    }
}