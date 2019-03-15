<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Upload
 *
 * @property integer $id
 * @property string $name
 * @property string $file_name
 * @property string $file_size
 * @property string $mime
 * @property string $extension
 * @property string $user_id
 * @property string $url
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Query\Builder|\App\Upload whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Upload whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Upload whereFileName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Upload whereFileSize($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Upload whereMime($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Upload whereExtension($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Upload whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Upload whereUrl($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Upload whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Upload whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Upload notBelongingToReply($id)
 * @mixin \Eloquent
 * @property string $thumbnail_url
 * @method static \Illuminate\Database\Query\Builder|\App\Upload whereThumbnailUrl($value)
 * @property-read string $formatted_size
 * @property-read string $path
 */
class Upload extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
    ];

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden  = ['pivot'];

    /**
     * Custom attributes that should be appended to model.
     *
     * @var array
     */
    protected $appends = ['path', 'formatted_size'];

    /**
     * Many to many relationship with reply model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function replies()
    {
        return $this->morphedByMany('App\Reply', 'uploadable');
    }

    /**
     * Many to many relationship with reply model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function canned_replies()
    {
        return $this->morphedByMany('App\CannedReply', 'uploadable');
    }

    /**
     * Many to one relationship with user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Get relative file path for this upload.
     *
     * @return string
     */
    public function getPathAttribute()
    {
        if ( ! isset($this->attributes['file_name'])) return null;

        return 'uploads/'.$this->attributes['file_name'];
    }

    /**
     * Get url for previewing upload.
     *
     * @param string $value
     * @return string
     */
    public function getUrlAttribute($value)
    {
        if ($value) return $value;

        return 'secure/uploads/'.$this->attributes['id'];
    }

    /**
     * Get upload size in human readable format.
     *
     * @return string
     */
    public function getFormattedSizeAttribute()
    {
        if ( ! isset($this->attributes['file_size'])) return null;

        $size = $this->attributes['file_size'];

        if ($size >= 1<<30) {
            return number_format($size/(1<<30))."GB";
        } elseif ($size >= 1<<20) {
            return number_format($size/(1<<20))."MB";
        } elseif ($size >= 1<<10) {
            return number_format($size/(1<<10))."KB";
        } else {
            return number_format($size)." bytes";
        }
    }

    public function scopeNotBelongingToReply($q, $id)
    {
        if ( ! $id) return $q;

        return $q->whereDoesntHave('replies', function($qInner) use($id) {
            $qInner->where('replies.id', $id);
        });
    }
}
