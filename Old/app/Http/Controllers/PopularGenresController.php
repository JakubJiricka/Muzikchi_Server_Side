<?php namespace App\Http\Controllers;

use App;
use App\Genre;
use Cache;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Services\Providers\ProviderResolver;
use Vebto\Bootstrap\Controller;
use Vebto\Settings\Settings;

class PopularGenresController extends Controller
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
     * @return Collection
     */
    public function index()
    {
        $this->authorize('index', Genre::class);

        return Cache::remember('genres.popular', $this->getCacheTime(), function() {
            $genres = $this->resolver->get('genres')->getGenres();
            return ! empty($genres) ? $genres : null;
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