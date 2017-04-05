<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\APIRequests
 *
 * @property int $id
 * @property int $user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\APIRequests whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\APIRequests whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\APIRequests whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\APIRequests whereUserId($value)
 * @mixin \Eloquent
 */
class APIRequests extends Model
{
    protected $table = 'api_requests';

    protected $fillable = [
        'user_id',
    ];
}
