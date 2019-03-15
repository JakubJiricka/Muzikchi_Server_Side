<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\SocialProfile
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $service_name
 * @property string $user_service_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Query\Builder|\App\SocialProfile whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SocialProfile whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SocialProfile whereServiceName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SocialProfile whereUserServiceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SocialProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SocialProfile whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $username
 * @method static \Illuminate\Database\Query\Builder|\App\SocialProfile whereUsername($value)
 */
class SocialProfile extends Model {

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
