<?php namespace App\Services\Providers\Local;

use App\Genre;
use App\Services\Providers\Lastfm\LastfmGenres;
use Vebto\Settings\Settings;
use Illuminate\Database\Eloquent\Collection;

class LocalGenres {

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var Genre
     */
    private $genre;

    /**
     * @var LastfmGenres
     */
    private $lastfmGenres;

    /**
     * Create new LocalGenres instance.
     *
     * @param Settings $settings
     * @param Genre $genre
     * @param LastfmGenres $lastfmGenres
     */
    public function __construct(Settings $settings, Genre $genre, LastfmGenres $lastfmGenres)
    {
        $this->genre = $genre;
        $this->settings = $settings;
        $this->lastfmGenres = $lastfmGenres;
    }

    /**
     * Get genres using local provider.
     *
     * @return Collection
     */
    public function getGenres() {
        $names = json_decode($this->settings->get('homepage.genres'), true);

        if ( ! $names) return collect();

        $genres = $this->genre->whereIn('name', $names)->get();

        $newGenres = $this->maybeCreateNonExistentGenres($names, $genres);

        $genres = $genres->merge($newGenres)->map(function(Genre $genre) {
            $genre['image'] = $this->lastfmGenres->getLocalImagePath($genre->name);
            return $genre;
        });

        return $genres->sort(function(Genre $current, Genre $previous) use($names) {
            return array_search($current->name, $names) > array_search($previous->name, $names);
        })->values();
    }

    /**
     * Create genres with specified names, if they don't exist already.
     *
     * @param array $names
     * @param Collection $genres
     * @return Collection
     */
    private function maybeCreateNonExistentGenres($names, Collection $genres)
    {
        $names = array_filter($names, function($name) use($genres) {
            return ! $genres->first(function(Genre $genre) use($name) {
                return strtolower($genre->name) === strtolower($name);
            });
        });

        if (empty($names)) return collect();

        $newGenres = array_map(function($name) {
            return ['name' => $name];
        }, $names);

        try {
            $this->genre->insert($newGenres);
        } catch (\Exception $e) {
            //
        }

        return $this->genre->whereIn('name', $names)->get();
    }

    public function getGenreArtists(Genre $genre)
    {
        return null;
    }
}