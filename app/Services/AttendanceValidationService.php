<?php

use App\Models\Attendance;
use App\Models\EmployeeSchedule;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\WorkCalendar;

class AttendanceValidationService
{
    public function validate(User $user)
    {
        return [

            'device' => $this->validateDevice($user),

            'schedule' => $this->validateSchedule($user),

            'holiday' => $this->validateHoliday(),

            'location' => $this->validateLocation($user),

            'duplicate' => $this->validateDuplicate($user),

        ];
    }

    private function validateDevice(User $user)
    {
        $uuid = request('device_uuid');

        return UserDevice::where([
            'user_id' => $user->id,
            'device_uuid' => $uuid,
            'status' => 'approved',
        ])->exists();
    }

    private function validateSchedule(User $user)
    {
        return EmployeeSchedule::where([
            'user_id' => $user->id,
            'date' => today(),
        ])->exists();
    }

    private function validateHoliday()
    {
        $calendar = WorkCalendar::whereDate('date', today())->first();

        if (! $calendar) {
            return true;
        }

        return ! $calendar->is_holiday;
    }

    private function validateLocation(User $user)
    {
        $branch = $user->branch;

        $distance = $this->distance(
            request('latitude'),
            request('longitude'),
            $branch->latitude,
            $branch->longitude
        );

        return $distance <= $branch->radius;
    }

    private function distance($lat1, $lon1, $lat2, $lon2)
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

    private function validateDuplicate(User $user)
    {
        return ! Attendance::where([
            'user_id' => $user->id,
            'date' => today(),
        ])->exists();
    }
}
