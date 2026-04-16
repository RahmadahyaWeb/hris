<?php

namespace App\Services;

use App\Models\AttendanceLog;

class AttendanceService
{
    public function getTodayLogs(int $userId)
    {
        return AttendanceLog::where('user_id', $userId)
            ->whereDate('recorded_at', now()->toDateString())
            ->orderBy('recorded_at')
            ->get();
    }

    public function getState(int $userId): array
    {
        $logs = $this->getTodayLogs($userId);

        $checkin = $logs->firstWhere('type', 'checkin');
        $checkout = $logs->lastWhere('type', 'checkout');

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

    public function checkIn(int $userId, float $lat, float $lng)
    {
        $state = $this->getState($userId);

        if (! $this->canCheckIn($state)) {
            throw new \Exception('Already checked in');
        }

        // TODO: validate GPS (next step)

        return AttendanceLog::create([
            'user_id' => $userId,
            'type' => 'checkin',
            'latitude' => $lat,
            'longitude' => $lng,
            'recorded_at' => now(),
        ]);
    }

    public function checkOut(int $userId, float $lat, float $lng)
    {
        $state = $this->getState($userId);

        if (! $this->canCheckOut($state)) {
            throw new \Exception('Cannot checkout');
        }

        return AttendanceLog::create([
            'user_id' => $userId,
            'type' => 'checkout',
            'latitude' => $lat,
            'longitude' => $lng,
            'recorded_at' => now(),
        ]);
    }

    public function startBreak(int $userId, float $lat, float $lng)
    {
        $state = $this->getState($userId);

        if (! $this->canStartBreak($state)) {
            throw new \Exception('Cannot start break');
        }

        return AttendanceLog::create([
            'user_id' => $userId,
            'type' => 'break_start',
            'latitude' => $lat,
            'longitude' => $lng,
            'recorded_at' => now(),
        ]);
    }

    public function endBreak(int $userId, float $lat, float $lng)
    {
        $state = $this->getState($userId);

        if (! $this->canEndBreak($state)) {
            throw new \Exception('Cannot end break');
        }

        return AttendanceLog::create([
            'user_id' => $userId,
            'type' => 'break_end',
            'latitude' => $lat,
            'longitude' => $lng,
            'recorded_at' => now(),
        ]);
    }
}
