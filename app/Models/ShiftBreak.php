<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftBreak extends Model
{
    protected $fillable = [
        'shift_id',
        'start_time',
        'end_time',
        'duration_minutes',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
