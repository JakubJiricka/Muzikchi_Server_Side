<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Social
 *
 * @property int $id
 * @property int $user_id
 * @property string $service
 * @property string $token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Social whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Social whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Social whereService($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Social whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Social whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Social whereUserId($value)
 * @mixin \Eloquent
 */
class Social extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users_oauth';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['service', 'token', 'user_id'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
