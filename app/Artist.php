<?php namespace App;

use App\Traits\OrdersByPopularity;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Artist
 *
 * @property int $id
 * @property string $name
 * @property int|null $spotify_followers
 * @property int $spotify_popularity
 * @property string $image_small
 * @property string|null $image_large
 * @property int $fully_scraped
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $bio
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Album[] $albums
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Genre[] $genres
 * @property-read string $image_big
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Artist[] $similar
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Artist whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Artist whereFullyScraped($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Artist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Artist whereImageLarge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Artist whereImageSmall($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Artist whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Artist whereSpotifyFollowers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Artist whereSpotifyPopularity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Artist whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Artist extends Model {

    use OrdersByPopularity;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'spotify_popularity' => 'integer',
        'fully_scraped' => 'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['fully_scraped', 'temp_id', 'pivot'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'views'];

    public function albums()
    {
    	return $this->hasMany('App\Album');
    }

    public function similar()
    {
        return $this->belongsToMany('App\Artist', 'similar_artists', 'artist_id', 'similar_id')->orderByPopularity('desc');
    }

    /**
     * Many to many relationship with genre model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function genres()
    {
        return $this->belongsToMany('App\Genre', 'genre_artist');
    }

    /**
     * Decode artist biography attribute.
     *
     * @param string $value
     * @return array
     */
    public function getBioAttribute($value) {
        if ( ! $value) return [];
        return json_decode($value, true);
    }

    /**
     * Get small artist image or default image.
     *
     * @param $value
     * @return string
     */
    public function getImageSmallAttribute($value)
    {
        if ($value) return $value;

        return asset('client/assets/images/default/artist_small.jpg');
    }

    /**
     * Get large artist image or default image.
     *
     * @param $value
     * @return string
     */
    public function getImageLargeAttribute($value)
    {
        if ($value) return $value;

        return asset('client/assets/images/default/artist-big.png');
    }
}
