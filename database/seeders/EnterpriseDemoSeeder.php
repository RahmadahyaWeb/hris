<?php

namespace Database\Seeders;

use App\Models\ApprovalStep;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\Branch;
use App\Models\Division;
use App\Models\EmployeeSchedule;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Position;
use App\Models\Shift;
use App\Models\ShiftBreak;
use App\Models\User;
use App\Services\AttendanceStateService;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EnterpriseDemoSeeder extends Seeder
{
    public function run(): void
    {
        try {

            DB::beginTransaction();

            /*
            |------------------------------------------------------------
            | MASTER DATA
            |------------------------------------------------------------
            */

            $branch = Branch::firstOrCreate(
                ['name' => 'Head Office'],
                [
                    'latitude' => -3.3186,
                    'longitude' => 114.5944,
                    'radius' => 100,
                ]
            );

            $engineering = Division::firstOrCreate(['name' => 'Engineering']);
            $sales = Division::firstOrCreate(['name' => 'Sales']);

            /*
            |------------------------------------------------------------
            | POSITIONS (WITH PARENT)
            |------------------------------------------------------------
            */

            $engineeringManager = Position::firstOrCreate([
                'division_id' => $engineering->id,
                'title' => 'Manager',
            ], [
                'parent_id' => null,
            ]);

            $engineeringStaff = Position::firstOrCreate([
                'division_id' => $engineering->id,
                'title' => 'Staff',
            ], [
                'parent_id' => $engineeringManager->id,
            ]);

            $salesManager = Position::firstOrCreate([
                'division_id' => $sales->id,
                'title' => 'Manager',
            ], [
                'parent_id' => null,
            ]);

            $salesStaff = Position::firstOrCreate([
                'division_id' => $sales->id,
                'title' => 'Staff',
            ], [
                'parent_id' => $salesManager->id,
            ]);

            /*
            |------------------------------------------------------------
            | USERS (4)
            |------------------------------------------------------------
            */

            $engManagerUser = User::firstOrCreate(
                ['email' => 'eng.manager@example.com'],
                [
                    'name' => 'Engineering Manager',
                    'password' => Hash::make('password'),
                    'branch_id' => $branch->id,
                    'position_id' => $engineeringManager->id,
                ]
            );

            $engStaffUser = User::firstOrCreate(
                ['email' => 'eng.staff@example.com'],
                [
                    'name' => 'Engineering Staff',
                    'password' => Hash::make('password'),
                    'branch_id' => $branch->id,
                    'position_id' => $engineeringStaff->id,
                ]
            );

            $salesManagerUser = User::firstOrCreate(
                ['email' => 'sales.manager@example.com'],
                [
                    'name' => 'Sales Manager',
                    'password' => Hash::make('password'),
                    'branch_id' => $branch->id,
                    'position_id' => $salesManager->id,
                ]
            );

            $salesStaffUser = User::firstOrCreate(
                ['email' => 'sales.staff@example.com'],
                [
                    'name' => 'Sales Staff',
                    'password' => Hash::make('password'),
                    'branch_id' => $branch->id,
                    'position_id' => $salesStaff->id,
                ]
            );

            /*
            |------------------------------------------------------------
            | SHIFT + BREAK
            |------------------------------------------------------------
            */

            $morningShift = Shift::firstOrCreate(
                ['name' => 'Morning'],
                [
                    'start_time' => '08:00:00',
                    'end_time' => '16:00:00',
                    'cross_midnight' => false,
                ]
            );

            $nightShift = Shift::firstOrCreate(
                ['name' => 'Night'],
                [
                    'start_time' => '22:00:00',
                    'end_time' => '04:30:00',
                    'cross_midnight' => true,
                ]
            );

            ShiftBreak::updateOrCreate(
                ['shift_id' => $morningShift->id],
                [
                    'start_time' => '12:00:00',
                    'end_time' => '13:00:00',
                    'duration_minutes' => 60,
                ]
            );

            ShiftBreak::updateOrCreate(
                ['shift_id' => $nightShift->id],
                [
                    'start_time' => '01:00:00',
                    'end_time' => '01:30:00',
                    'duration_minutes' => 30,
                ]
            );

            /*
            |------------------------------------------------------------
            | CALENDAR
            |------------------------------------------------------------
            */

            $start = now()->startOfMonth();

            for ($i = 0; $i < 23; $i++) {

                $date = $start->copy()->addDays($i);

                DB::table('work_calendars')->insertOrIgnore([
                    'date' => $date->toDateString(),
                    'is_holiday' => $date->isWeekend(),
                    'description' => $date->isWeekend() ? 'Weekend' : null,
                ]);
            }

            /*
            |------------------------------------------------------------
            | SCHEDULE + ATTENDANCE
            |------------------------------------------------------------
            */

            $stateService = new AttendanceStateService;

            $users = [
                [$engManagerUser, $morningShift],
                [$engStaffUser, $nightShift],
                [$salesManagerUser, $morningShift],
                [$salesStaffUser, $nightShift],
            ];

            for ($i = 0; $i < 23; $i++) {

                $date = $start->copy()->addDays($i);

                if ($date->isWeekend()) {
                    continue;
                }

                foreach ($users as [$user, $shift]) {

                    $schedule = EmployeeSchedule::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'date' => $date->toDateString(),
                        ],
                        [
                            'shift_id' => $shift->id,
                        ]
                    );

                    if (rand(1, 100) <= 20) {
                        continue;
                    }

                    $shiftStart = $date->copy()->setTimeFromTimeString($shift->start_time);
                    $shiftEnd = $date->copy()->setTimeFromTimeString($shift->end_time);

                    if ($shift->cross_midnight && $shiftEnd->lte($shiftStart)) {
                        $shiftEnd->addDay();
                    }

                    $checkin = $shiftStart->copy()->addMinutes(rand(-5, 45));
                    $checkout = $shiftEnd->copy()->addMinutes(rand(-20, 120));

                    if ($checkout->lt($checkin)) {
                        $checkout->addDay();
                    }

                    $attendance = Attendance::create([
                        'user_id' => $user->id,
                        'date' => $date->toDateString(),
                        'checkin_at' => $checkin,
                        'checkout_at' => $checkout,
                    ]);

                    $shiftBreak = $shift->breaks()->first();

                    if ($shiftBreak) {

                        $breakStart = $date->copy()->setTimeFromTimeString($shiftBreak->start_time);
                        $breakEnd = $date->copy()->setTimeFromTimeString($shiftBreak->end_time);

                        if ($shift->cross_midnight && $breakEnd->lte($breakStart)) {
                            $breakEnd->addDay();
                        }

                        AttendanceBreak::create([
                            'attendance_id' => $attendance->id,
                            'start_at' => $breakStart,
                            'end_at' => $breakEnd,
                            'duration_minutes' => $breakStart->diffInMinutes($breakEnd),
                        ]);
                    }

                    $result = $stateService->resolve(
                        $attendance->fresh(['breaks']),
                        $schedule
                    );

                    $attendance->update([
                        'state' => $result['state'],
                        'late_minutes' => $result['late_minutes'],
                        'work_minutes' => $result['work_minutes'],
                        'overtime_minutes' => $result['overtime_minutes'],
                    ]);
                }
            }

            /*
            |------------------------------------------------------------
            | LEAVE
            |------------------------------------------------------------
            */

            $leave = LeaveType::firstOrCreate(['name' => 'Annual Leave']);

            ApprovalStep::firstOrCreate([
                'leave_type_id' => $leave->id,
                'step_order' => 1,
                'approver_type' => 'manager',
            ]);

            ApprovalStep::firstOrCreate([
                'leave_type_id' => $leave->id,
                'step_order' => 2,
                'approver_type' => 'hr',
            ]);

            foreach ([
                $engManagerUser,
                $engStaffUser,
                $salesManagerUser,
                $salesStaffUser,
            ] as $user) {

                LeaveBalance::firstOrCreate([
                    'user_id' => $user->id,
                    'leave_type_id' => $leave->id,
                    'year' => now()->year,
                ], [
                    'total_days' => 12,
                    'used_days' => 0,
                    'remaining_days' => 12,
                ]);
            }

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();
            throw $e;
        }
    }
}
