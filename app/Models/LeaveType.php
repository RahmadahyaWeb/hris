<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = [
        'name',
        'annual_quota',
        'is_paid',
    ];

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function approvalSteps()
    {
        return $this->hasMany(ApprovalStep::class)
            ->orderBy('step_order');
    }
}
