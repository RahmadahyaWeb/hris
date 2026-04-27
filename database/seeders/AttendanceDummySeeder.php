<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceDummySeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {

            $startDate = Carbon::create(2026, 4, 1);
            $endDate = Carbon::create(2026, 4, 21);

            // EXCLUDE SUPER ADMIN
            $users = User::whereDoesntHave('roles', function ($q) {
                $q->where('name', 'super_admin');
            })->pluck('id');

            foreach ($users as $userId) {

                $current = $startDate->copy();

                while ($current->lte($endDate)) {

                    $dayOfWeek = $current->dayOfWeekIso;

                    // skip weekend
                    if (in_array($dayOfWeek, [6, 7])) {
                        $current->addDay();

                        continue;
                    }

                    // random absent (10%)
                    if (rand(1, 100) <= 10) {

                        Attendance::updateOrCreate(
                            [
                                'user_id' => $userId,
                                'date' => $current->toDateString(),
                            ],
                            [
                                'status' => 'absent',
                            ]
                        );

                        $current->addDay();

                        continue;
                    }

                    // CHECKIN
                    $checkinTime = $current->copy()->setTime(8, rand(0, 20));

                    AttendanceLog::create([
                        'user_id' => $userId,
                        'type' => 'checkin',
                        'latitude' => -3.319437,
                        'longitude' => 114.590752,
                        'recorded_at' => $checkinTime,
                    ]);

                    // BREAK START
                    $breakStart = $current->copy()->setTime(12, rand(0, 10));

                    AttendanceLog::create([
                        'user_id' => $userId,
                        'type' => 'break_start',
                        'latitude' => -3.319437,
                        'longitude' => 114.590752,
                        'recorded_at' => $breakStart,
                    ]);

                    // BREAK END
                    $breakEnd = $breakStart->copy()->addMinutes(60);

                    AttendanceLog::create([
                        'user_id' => $userId,
                        'type' => 'break_end',
                        'latitude' => -3.319437,
                        'longitude' => 114.590752,
                        'recorded_at' => $breakEnd,
                    ]);

                    // CHECKOUT
                    $checkoutTime = $current->copy()->setTime(17, rand(0, 20));

                    AttendanceLog::create([
                        'user_id' => $userId,
                        'type' => 'checkout',
                        'latitude' => -3.319437,
                        'longitude' => 114.590752,
                        'recorded_at' => $checkoutTime,
                    ]);

                    // FINAL ATTENDANCE
                    $workMinutes = $checkinTime->diffInMinutes($checkoutTime) - 60;

                    $late = $checkinTime->gt($current->copy()->setTime(8, 0))
                        ? $current->copy()->setTime(8, 0)->diffInMinutes($checkinTime)
                        : 0;

                    $overtime = $checkoutTime->gt($current->copy()->setTime(17, 0))
                        ? $current->copy()->setTime(17, 0)->diffInMinutes($checkoutTime)
                        : 0;

                    Attendance::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'date' => $current->toDateString(),
                        ],
                        [
                            'status' => 'present',
                            'checkin_at' => $checkinTime,
                            'checkout_at' => $checkoutTime,
                            'work_minutes' => max(0, $workMinutes),
                            'break_minutes' => 60,
                            'late_minutes' => $late,
                            'early_leave_minutes' => 0,
                            'overtime_minutes' => $overtime,
                        ]
                    );

                    $current->addDay();
                }
            }

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
