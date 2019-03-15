<?php namespace App;

use App\Traits\FormatsPermissions;
use Illuminate\Database\Eloquent\Model;


class Score extends Model
{
    protected $table = 'tot_score';

    protected $quarded = ['id'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['track_id', 'user_id', 'score'];

}
