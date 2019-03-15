<?php namespace App\Services\Providers\Lastfm;

use App;
use App\Artist;
use Vebto\Settings\Settings;
use Illuminate\Support\Str;
use App\Services\HttpClient;
use App\Services\Artists\ArtistSaver;
use Illuminate\Support\Collection;
use App\Services\Providers\Spotify\SpotifyArtist;

class LastfmGenres {

    /**
     * Links of artist placeholder images on last.fm
     *
     * @var array
     */
    private $lastfmPlaceholderImages = [
        'https://lastfm-img2.akamaized.net/i/u/289e0f7b270445e5c550714f606fd8fd.png'
    ];

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var SpotifyArtist
     */
    private $spotifyArtist;

    /**
     * @var ArtistSaver
     */
    private $saver;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * Create new LastfmGenres instance.
     *
     * @param SpotifyArtist $spotifyArtist
     * @param ArtistSaver $saver
     * @param Settings $settings
     */
    public function __construct(SpotifyArtist $spotifyArtist, ArtistSaver $saver, Settings $settings)
    {
        $this->httpClient = new HttpClient(['base_uri' => 'http://ws.audioscrobbler.com/2.0/']);
        $this->spotifyArtist = $spotifyArtist;
        $this->saver = $saver;
        $this->settings = $settings;
        $this->apiKey = config('vebto.site.lastfm.key');

        ini_set('max_execution_time', 0);
    }

    public function getGenres()
    {
        $names = json_decode($this->settings->get('homepage.genres'), true);

        if ($names && ! empty($names)) {
            return $this->formatGenres($names)['formatted'];
        } else {
            return $this->getMostPopular();
        }
    }

    public function getMostPopular()
    {
        $response = $this->httpClient->get("?method=tag.getTopTags&api_key=$this->apiKey&format=json");

        if ( ! isset($response['toptags'])) {
            sleep(3);
            $response = $this->httpClient->get("?method=tag.getTopTags&api_key=$this->apiKey&format=json");
        }

        $formatted = $this->formatGenres($response['toptags']['tag']);

        $this->saver->saveOrUpdate($formatted['names'], array_flatten($formatted['names']), 'genres');

        return $formatted['formatted'];
    }

    public function formatGenres($genres) {
        $formatted = [];
        $names     = [];

        if (is_string($genres)) {
            $genres = explode(',', $genres);
        }

        foreach($genres as $genre) {
            if (is_array($genre)) {
                $formatted[] = ['name' => $genre['name'], 'popularity' => $genre['count'], 'image' => $this->getLocalImagePath($genre['name'])];
                $names[] = ['name' => $genre['name']];
            } else {
                $genre = trim($genre);
                $formatted[] = ['name' => $genre, 'popularity' => 0, 'image' => $this->getLocalImagePath($genre)];
                $names = $genres;
            }
        }

        return ['formatted' => $formatted, 'names' => $names];
    }

    public function getGenreArtists($genre)
    {
        $genreName = $genre['name'];
        $response  = $this->httpClient->get("?method=tag.gettopartists&tag=$genreName&api_key=$this->apiKey&format=json&limit=50");
        $artists   = $response['topartists']['artist'];
        $names     = [];
        $formatted = [];

        foreach($artists as $artist) {
            if ( ! $this->collectionContainsArtist($artist['name'], $formatted)) {

                $img = ! in_array($artist['image'][4]['#text'], $this->lastfmPlaceholderImages) ? $artist['image'][4]['#text'] : null;

                $formatted[] = [
                    'name' => $artist['name'],
                    'image_small' => $img,
                    'fully_scraped' => 0,
                ];

                $names[] = $artist['name'];
            }
        }

        $existing = Artist::whereIn('name', $names)->get();

        $insert = array_filter($formatted, function($artist) use ($existing) {
            return ! $this->collectionContainsArtist($artist['name'], $existing);
        });

        try {
            Artist::insert($insert);
        } catch(\Exception $e) {
            //
        }

        $artists = Artist::whereIn('name', $names)->get();

        $this->attachGenre($artists, $genre);

        return $artists;
    }

    /**
     * Attach genre to artists in database.
     *
     * @param Collection $artists
     * @param App\Genre $genre
     */
    private function attachGenre($artists, $genre)
    {
        $pivotInsert = [];

        foreach ($artists as $artist) {
            $pivotInsert[] = ['genre_id' => $genre['id'], 'artist_id' => $artist['id']];
        }

        $this->saver->saveOrUpdate($pivotInsert, array_flatten($pivotInsert), 'genre_artist');
    }

    public function getLocalImagePath($genreName)
    {
        $genreName = str_replace(' ', '-', strtolower(trim($genreName)));

        return 'images/genres/'.$genreName.'.jpg';
    }

    private function collectionContainsArtist($name, $collection) {
        foreach ($collection as $artist) {
            if ($this->normalizeName($name) === $this->normalizeName($artist['name'])) {
                return true;
            }
        }

        return false;
    }

    private function normalizeName($name)
    {
        return trim(Str::ascii(mb_strtolower($name)));
    }
}