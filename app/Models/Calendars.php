<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calendars extends Model
{

	protected $table = 'calendars';
    protected $guarded = [];

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $casts = [
        'id' => 'string',
    ];
}
