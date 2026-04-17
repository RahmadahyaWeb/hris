<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\BreakRule;
use App\Models\EmployeeAssignment;
use App\Models\EmployeeSchedule;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\WorkScheduleDay;
use Carbon\Carbon;
use Exception;

class AttendanceService
{
    public function getTodayLogs(int $userId)
    {
        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();

        return AttendanceLog::where('user_id', $userId)
            ->where(function ($q) use ($today, $tomorrow) {
                $q->whereDate('recorded_at', $today)
                    ->orWhereDate('recorded_at', $tomorrow);
            })
            ->orderBy('recorded_at')
            ->get();
    }

    public function getState(int $userId): array
    {
        $logs = $this->getTodayLogs($userId);

        $checkin = $logs->firstWhere('type', 'checkin');
        $checkout = $logs->where('type', 'checkout')->last();

        $breakStartCount = $logs->where('type', 'break_start')->count();
        $breakEndCount = $logs->where('type', 'break_end')->count();

        $isOnBreak = $breakStartCount > $breakEndCount;

        return [
            'has_checkin' => (bool) $checkin,
            'has_checkout' => (bool) $checkout,
            'is_on_break' => $isOnBreak,
            'checkin_at' => $checkin?->recorded_at,
            'checkout_at' => $checkout?->recorded_at,
            'logs' => $logs,
        ];
    }

    public function canCheckIn(array $state): bool
    {
        return ! $state['has_checkin'];
    }

    public function canCheckOut(array $state): bool
    {
        return $state['has_checkin'] && ! $state['has_checkout'];
    }

    public function canStartBreak(array $state): bool
    {
        return $state['has_checkin']
            && ! $state['has_checkout']
            && ! $state['is_on_break'];
    }

    public function canEndBreak(array $state): bool
    {
        return $state['is_on_break'];
    }

    public function checkIn(int $userId, ?float $lat, ?float $lng)
    {
        $state = $this->getState($userId);

        if (! $this->canCheckIn($state)) {
            throw new Exception('Already checked in');
        }

        $this->ensureLocation($lat, $lng);
        $this->validateWorkingDay($userId);
        $this->validateHoliday($userId);
        $this->validateLeave($userId);
        $this->validateShiftTime($userId, 'checkin');
        $this->validateGps($userId, $lat, $lng);

        return AttendanceLog::create([
            'user_id' => $userId,
            'type' => 'checkin',
            'latitude' => $lat,
            'longitude' => $lng,
            'recorded_at' => now(),
        ]);
    }

    public function checkOut(int $userId, ?float $lat, ?float $lng)
    {
        $state = $this->getState($userId);

        if (! $this->canCheckOut($state)) {
            throw new Exception('Cannot checkout');
        }

        $this->ensureLocation($lat, $lng);
        $this->validateWorkingDay($userId);
        $this->validateHoliday($userId);
        $this->validateLeave($userId);
        $this->validateShiftTime($userId, 'checkout');
        $this->validateGps($userId, $lat, $lng);

        return AttendanceLog::create([
            'user_id' => $userId,
            'type' => 'checkout',
            'latitude' => $lat,
            'longitude' => $lng,
            'recorded_at' => now(),
        ]);
    }

    public function startBreak(int $userId, ?float $lat, ?float $lng)
    {
        $state = $this->getState($userId);

        if (! $this->canStartBreak($state)) {
            throw new Exception('Cannot start break');
        }

        $this->ensureLocation($lat, $lng);
        $this->validateWorkingDay($userId);
        $this->validateHoliday($userId);
        $this->validateLeave($userId);
        $this->validateGps($userId, $lat, $lng);

        $this->validateBreakRule($userId);

        return AttendanceLog::create([
            'user_id' => $userId,
            'type' => 'break_start',
            'latitude' => $lat,
            'longitude' => $lng,
            'recorded_at' => now(),
        ]);
    }

    public function endBreak(int $userId, ?float $lat, ?float $lng)
    {
        $state = $this->getState($userId);

        if (! $this->canEndBreak($state)) {
            throw new Exception('Cannot end break');
        }

        $this->ensureLocation($lat, $lng);
        $this->validateGps($userId, $lat, $lng);

        $rule = $this->validateBreakRule($userId);

        $logs = $this->getTodayLogs($userId);
        $lastBreakStart = $logs->where('type', 'break_start')->last();

        if ($rule->duration_minutes && $lastBreakStart) {
            $start = Carbon::parse($lastBreakStart->recorded_at);
            $duration = $start->diffInMinutes(now());

            if ($duration > $rule->duration_minutes) {
                throw new Exception('Break exceeded allowed duration');
            }
        }

        return AttendanceLog::create([
            'user_id' => $userId,
            'type' => 'break_end',
            'latitude' => $lat,
            'longitude' => $lng,
            'recorded_at' => now(),
        ]);
    }

    protected function ensureLocation(?float $lat, ?float $lng): void
    {
        if (! $lat || ! $lng) {
            throw new Exception('Location not detected');
        }
    }

    protected function validateWorkingDay(int $userId): void
    {
        $day = $this->getWorkScheduleDay($userId);

        if (! $day || ! $day->is_working_day) {
            throw new Exception('Today is not a working day');
        }
    }

    protected function validateHoliday(int $userId): void
    {
        if (Holiday::whereDate('date', now())->exists()) {
            throw new Exception('Today is a holiday');
        }
    }

    protected function validateLeave(int $userId): void
    {
        $today = now()->toDateString();

        if (Leave::where('user_id', $userId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->exists()) {
            throw new Exception('You are on leave');
        }
    }

    protected function validateShiftTime(int $userId, string $type): void
    {
        $shift = $this->getTodayShiftDetail($userId);

        if (! $shift) {
            throw new Exception('No shift assigned');
        }

        $now = now();
        $start = Carbon::parse($shift->start_time);
        $end = Carbon::parse($shift->end_time);

        if ($shift->is_overnight) {
            $end->addDay();
        }

        if ($type === 'checkin') {
            if ($now->lt($start->subMinutes($shift->tolerance_late ?? 0))) {
                throw new Exception('Too early to check-in');
            }
        }

        if ($type === 'checkout') {
            if ($now->lt($start)) {
                throw new Exception('Cannot checkout before shift starts');
            }
        }
    }

    protected function validateBreakRule(int $userId): BreakRule
    {
        $shift = $this->getTodayShiftDetail($userId);

        if (! $shift) {
            throw new Exception('No shift assigned');
        }

        $rules = BreakRule::where('shift_id', $shift->id)->get();

        if ($rules->isEmpty()) {
            throw new Exception('No break rule defined');
        }

        $now = now()->format('H:i:s');

        foreach ($rules as $rule) {
            if ($rule->is_flexible) {
                return $rule;
            }

            if ($rule->start_time && $rule->end_time) {
                if ($now >= $rule->start_time && $now <= $rule->end_time) {
                    return $rule;
                }
            }
        }

        throw new Exception('Break not allowed at this time');
    }

    protected function getWorkScheduleDay(int $userId): ?WorkScheduleDay
    {
        $today = now()->toDateString();
        $dayOfWeek = now()->dayOfWeekIso;

        $schedule = EmployeeSchedule::with('workSchedule.days')
            ->where('user_id', $userId)
            ->whereDate('start_date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $today);
            })
            ->first();

        if (! $schedule) {
            return null;
        }

        return $schedule->workSchedule->days
            ->firstWhere('day_of_week', $dayOfWeek);
    }

    protected function getTodayShiftDetail(int $userId)
    {
        return $this->getWorkScheduleDay($userId)?->shift;
    }

    protected function validateGps(int $userId, float $lat, float $lng): void
    {
        $assignment = EmployeeAssignment::with('branch')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->first();

        if (! $assignment || ! $assignment->branch) {
            throw new Exception('No active branch assigned');
        }

        $distance = $this->calculateDistance(
            $lat,
            $lng,
            $assignment->branch->latitude,
            $assignment->branch->longitude
        );

        if ($distance > $assignment->branch->radius) {
            throw new Exception('You are outside allowed area');
        }
    }

    protected function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) *
            pow(sin($lonDelta / 2), 2)
        ));

        return $angle * $earthRadius;
    }
}
