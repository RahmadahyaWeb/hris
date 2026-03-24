<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceBreak extends Model
{
    protected $fillable = [
        'attendance_id',
        'start_at',
        'end_at',
        'duration_minutes',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
