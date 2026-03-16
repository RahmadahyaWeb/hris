<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkCalendar extends Model
{
    protected $fillable = [
        'date',
        'is_holiday',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'is_holiday' => 'boolean',
    ];
}
