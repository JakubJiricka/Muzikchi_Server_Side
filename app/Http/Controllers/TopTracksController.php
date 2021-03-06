<?php namespace App\Http\Controllers;

use App;
use App\Services\Providers\ProviderResolver;
use App\Track;
use Cache;
use Carbon\Carbon;
use Vebto\Bootstrap\Controller;
use Vebto\Settings\Settings;

class TopTracksController extends Controller
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
        $this->authorize('index', Track::class);

        return Cache::remember('tracks.top50', $this->getCacheTime(), function() {
            $tracks = $this->resolver->get('top_tracks')->getTopTracks();
            return ! empty($tracks) ? $tracks : null;
        });
    }
    
    public function latest()
    {
    	$tracks = Track::where('Illegal_or_not', '=', null)->orderBy('id', 'desc')->limit(100)->get()->toArray();
	    return ! empty($tracks) ? $tracks : null;
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
