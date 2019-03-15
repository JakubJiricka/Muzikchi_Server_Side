<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Genre
 *
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Artist[] $artists
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Genre whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Genre whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Genre whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Genre whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Genre extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'genres';

    protected $guarded = ['id'];

    /**
     * Many to many relationship with artist model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function artists()
    {
        return $this->belongsToMany('App\Artist', 'genre_artist')->orderByPopularity('desc')->orderBy('spotify_followers', 'desc');
    }
}
