<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'checkin_at',
        'checkout_at',
        'checkin_latitude',
        'checkin_longitude',
        'checkout_latitude',
        'checkout_longitude',
        'late_minutes',
        'work_minutes',
        'overtime_minutes',
        'state',
    ];

    protected $casts = [
        'checkin_at' => 'datetime',
        'checkout_at' => 'datetime',
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    /**
     * Total break minutes
     */
    public function getBreakMinutesAttribute(): int
    {
        return $this->breaks->sum('duration_minutes');
    }

    /**
     * Flags (untuk UI & summary konsisten)
     */
    public function getIsPresentAttribute(): bool
    {
        return ! is_null($this->checkin_at);
    }

    public function getIsLateAttribute(): bool
    {
        return $this->late_minutes > 0;
    }

    public function getIsOvertimeAttribute(): bool
    {
        return $this->overtime_minutes > 0;
    }

    public function getIsEarlyCheckoutAttribute(): bool
    {
        return $this->state === 'early_checkout';
    }
}
