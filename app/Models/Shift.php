<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'cross_midnight',
    ];

    protected $casts = [
        'cross_midnight' => 'boolean',
    ];
}
