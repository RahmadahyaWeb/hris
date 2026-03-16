<?php

namespace App\Services;

use App\Models\AttendanceRule;

class AttendanceRuleService
{
    public function get(): AttendanceRule
    {
        return AttendanceRule::first();
    }

    public function lateTolerance(): int
    {
        return $this->get()->late_tolerance_minutes;
    }

    public function earlyCheckoutTolerance(): int
    {
        return $this->get()->early_checkout_tolerance;
    }

    public function overtimeAfter(): int
    {
        return $this->get()->overtime_after_minutes;
    }

    public function allowEarlyCheckin(): bool
    {
        return $this->get()->allow_early_checkin;
    }
}
