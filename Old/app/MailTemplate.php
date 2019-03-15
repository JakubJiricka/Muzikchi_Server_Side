<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\MailTemplate
 *
 * @property int $id
 * @property string $file_name
 * @property string $display_name
 * @property string $subject
 * @property string $action
 * @property bool $base
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\MailTemplate whereAction($value)
 * @method static \Illuminate\Database\Query\Builder|\App\MailTemplate whereBase($value)
 * @method static \Illuminate\Database\Query\Builder|\App\MailTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\MailTemplate whereDisplayName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\MailTemplate whereFileName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\MailTemplate whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\MailTemplate whereSubject($value)
 * @method static \Illuminate\Database\Query\Builder|\App\MailTemplate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MailTemplate extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
}
