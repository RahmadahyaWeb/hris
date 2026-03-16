<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRule extends Model
{
    protected $fillable = [
        'late_tolerance_minutes',
        'early_checkout_tolerance',
        'overtime_after_minutes',
        'allow_early_checkin',
    ];
}
