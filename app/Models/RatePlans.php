<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatePlans extends Model
{

	protected $table = 'rateplans';
    protected $guarded = [];

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $casts = [
        'id' => 'string',
    ];
}
