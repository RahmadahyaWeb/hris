<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\EmployeeSchedule;
use App\Models\Leave;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\WorkCalendar;

class AttendanceValidationService
{
    public function validate(User $user, string $uuid, float $lat, float $long, string $mode): array
    {
        return [

            'device' => $this->validateDevice($user, $uuid),

            'schedule' => $this->validateSchedule($user),

            'holiday' => $this->validateHoliday(),

            'location' => $this->validateLocation($user, $lat, $long),

            'duplicate' => $mode === 'checkin'
                ? $this->validateCheckin($user)
                : $this->validateCheckout($user),

        ];
    }

    private function validateDevice(User $user, string $uuid): bool
    {
        return UserDevice::where([
            'user_id' => $user->id,
            'device_uuid' => $uuid,
            'status' => 'approved',
        ])->exists();
    }

    private function validateSchedule(User $user): bool
    {
        $hasSchedule = EmployeeSchedule::where([
            'user_id' => $user->id,
            'date' => today(),
        ])->exists();

        $isOnLeave = Leave::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->exists();

        return $hasSchedule && ! $isOnLeave;
    }

    private function validateHoliday(): bool
    {
        $calendar = WorkCalendar::whereDate('date', today())->first();

        if (! $calendar) {
            return true;
        }

        return ! $calendar->is_holiday;
    }

    private function validateLocation(User $user, float $lat, float $long): bool
    {
        $branch = $user->branch;

        logger()->info('Location validation', [
            'user_lat' => $lat,
            'user_lng' => $long,
            'branch_lat' => $branch->latitude,
            'branch_lng' => $branch->longitude,
        ]);

        $distance = $this->distance(
            $lat,
            $long,
            $branch->latitude,
            $branch->longitude
        );

        return $distance <= $branch->radius;
    }

    private function distance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earth * $c;
    }

    private function validateCheckin(User $user): bool
    {
        return ! Attendance::where([
            'user_id' => $user->id,
            'date' => today(),
        ])->exists();
    }

    private function validateCheckout(User $user): bool
    {
        $attendance = Attendance::where([
            'user_id' => $user->id,
            'date' => today(),
        ])->first();

        if (! $attendance) {
            return false;
        }

        if (! $attendance->checkin_at) {
            return false;
        }

        if ($attendance->checkout_at) {
            return false;
        }

        return true;
    }
}
