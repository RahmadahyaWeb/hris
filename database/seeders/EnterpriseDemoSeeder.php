<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Division;
use App\Models\EmployeeSchedule;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Position;
use App\Models\Shift;
use App\Models\User;
use App\Models\WorkCalendar;
use App\Services\AttendanceStateService;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EnterpriseDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {

            /*
            |------------------------------------------------------------
            | Branch
            |------------------------------------------------------------
            */

            $branch = Branch::firstOrCreate([
                'name' => 'Head Office',
            ], [
                'latitude' => -3.3186000,
                'longitude' => 114.5944000,
                'radius' => 200,
            ]);

            /*
            |------------------------------------------------------------
            | Divisions
            |------------------------------------------------------------
            */

            $engineering = Division::firstOrCreate(['name' => 'Engineering']);
            $hr = Division::firstOrCreate(['name' => 'Human Resources']);
            $sales = Division::firstOrCreate(['name' => 'Sales']);

            /*
            |------------------------------------------------------------
            | Positions
            |------------------------------------------------------------
            */

            $positions = [

                Position::firstOrCreate([
                    'division_id' => $engineering->id,
                    'title' => 'Backend Developer',
                ]),

                Position::firstOrCreate([
                    'division_id' => $engineering->id,
                    'title' => 'Frontend Developer',
                ]),

                Position::firstOrCreate([
                    'division_id' => $engineering->id,
                    'title' => 'DevOps Engineer',
                ]),

                Position::firstOrCreate([
                    'division_id' => $hr->id,
                    'title' => 'HR Manager',
                ]),

                Position::firstOrCreate([
                    'division_id' => $sales->id,
                    'title' => 'Sales Executive',
                ]),

            ];

            /*
            |------------------------------------------------------------
            | Users
            |------------------------------------------------------------
            */

            $users = [

                User::firstOrCreate(
                    ['email' => 'backend@example.com'],
                    [
                        'name' => 'Backend Dev',
                        'password' => Hash::make('password'),
                        'branch_id' => $branch->id,
                        'position_id' => $positions[0]->id,
                    ]
                ),

                User::firstOrCreate(
                    ['email' => 'frontend@example.com'],
                    [
                        'name' => 'Frontend Dev',
                        'password' => Hash::make('password'),
                        'branch_id' => $branch->id,
                        'position_id' => $positions[1]->id,
                    ]
                ),

                User::firstOrCreate(
                    ['email' => 'devops@example.com'],
                    [
                        'name' => 'DevOps Engineer',
                        'password' => Hash::make('password'),
                        'branch_id' => $branch->id,
                        'position_id' => $positions[2]->id,
                    ]
                ),

                User::firstOrCreate(
                    ['email' => 'hr@example.com'],
                    [
                        'name' => 'HR Manager',
                        'password' => Hash::make('password'),
                        'branch_id' => $branch->id,
                        'position_id' => $positions[3]->id,
                    ]
                ),

                User::firstOrCreate(
                    ['email' => 'sales@example.com'],
                    [
                        'name' => 'Sales Executive',
                        'password' => Hash::make('password'),
                        'branch_id' => $branch->id,
                        'position_id' => $positions[4]->id,
                    ]
                ),

            ];

            /*
            |------------------------------------------------------------
            | Shift
            |------------------------------------------------------------
            */

            $shift = Shift::firstOrCreate([
                'name' => 'Morning',
            ], [
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'cross_midnight' => false,
            ]);

            /*
            |------------------------------------------------------------
            | Work Calendar
            |------------------------------------------------------------
            */

            $start = Carbon::create(now()->year, 2, 1);
            $end = now();

            $date = $start->copy();

            while ($date->lte($end)) {

                WorkCalendar::firstOrCreate([
                    'date' => $date->toDateString(),
                ], [
                    'is_holiday' => $date->isWeekend(),
                    'description' => $date->isWeekend() ? 'Weekend' : null,
                ]);

                $date->addDay();
            }

            /*
            |------------------------------------------------------------
            | Employee Schedule
            |------------------------------------------------------------
            */

            $date = $start->copy();

            while ($date->lte($end)) {

                if (! $date->isWeekend()) {

                    foreach ($users as $user) {

                        EmployeeSchedule::firstOrCreate([
                            'user_id' => $user->id,
                            'date' => $date->toDateString(),
                        ], [
                            'shift_id' => $shift->id,
                        ]);

                    }

                }

                $date->addDay();
            }

            /*
            |------------------------------------------------------------
            | Attendance (Realistic Data)
            |------------------------------------------------------------
            */

            $schedules = EmployeeSchedule::with('shift')->get();

            foreach ($schedules as $schedule) {

                $rand = rand(1, 100);

                if ($rand <= 5) {
                    continue; // absent
                }

                $date = Carbon::parse($schedule->date)->startOfDay();

                $shiftStart = $date->copy()
                    ->setTimeFromTimeString($schedule->shift->start_time);

                $shiftEnd = $date->copy()
                    ->setTimeFromTimeString($schedule->shift->end_time);

                if ($rand <= 70) {

                    $checkin = $shiftStart->copy()->addMinutes(rand(-5, 5));

                } elseif ($rand <= 85) {

                    $checkin = $shiftStart->copy()->addMinutes(rand(10, 40));

                } else {

                    $checkin = $shiftStart->copy()->addMinutes(rand(-5, 5));

                }

                if ($rand >= 90) {

                    $checkout = $shiftEnd->copy()->addMinutes(rand(30, 90));

                } else {

                    $checkout = $shiftEnd->copy()->addMinutes(rand(-10, 10));

                }

                $attendance = Attendance::create([
                    'user_id' => $schedule->user_id,
                    'date' => $schedule->date,
                    'checkin_at' => $checkin,
                    'checkout_at' => $checkout,
                ]);

                $stateService = new AttendanceStateService;

                $state = $stateService->resolve($attendance, $schedule);

                $attendance->update([

                    'state' => $state['state'],

                    'late_minutes' => max(0, $shiftStart->diffInMinutes($checkin, false)),

                    'work_minutes' => $checkin->diffInMinutes($checkout),

                    'overtime_minutes' => max(0, $shiftEnd->diffInMinutes($checkout, false)),

                ]);

            }

            /*
            |------------------------------------------------------------
            | Leave Types
            |------------------------------------------------------------
            */

            $annual = LeaveType::firstOrCreate(['name' => 'Annual Leave']);
            $sick = LeaveType::firstOrCreate(['name' => 'Sick Leave']);

            /*
            |------------------------------------------------------------
            | Leave Balance
            |------------------------------------------------------------
            */

            foreach ($users as $user) {

                LeaveBalance::firstOrCreate([
                    'user_id' => $user->id,
                    'leave_type_id' => $annual->id,
                    'year' => now()->year,
                ], [
                    'total_days' => 12,
                    'used_days' => 0,
                    'remaining_days' => 12,
                ]);

            }

            /*
            |------------------------------------------------------------
            | Leaves (Realistic Status)
            |------------------------------------------------------------
            */

            foreach ($users as $user) {

                $days = rand(1, 3);

                $statusPool = ['approved', 'pending', 'rejected'];

                $status = $statusPool[array_rand($statusPool)];

                $leave = Leave::create([

                    'user_id' => $user->id,

                    'leave_type_id' => $annual->id,

                    'start_date' => now()->subDays(rand(10, 20))->toDateString(),

                    'end_date' => now()->subDays(rand(5, 9))->toDateString(),

                    'days' => $days,

                    'reason' => 'Personal leave',

                    'status' => $status,

                    'approved_by' => $status === 'approved'
                        ? $users[3]->id
                        : null,

                    'approved_at' => $status === 'approved'
                        ? now()
                        : null,

                ]);

                if ($status === 'approved') {

                    $balance = LeaveBalance::where([
                        'user_id' => $user->id,
                        'leave_type_id' => $annual->id,
                        'year' => now()->year,
                    ])->first();

                    $balance->update([
                        'used_days' => $balance->used_days + $days,
                        'remaining_days' => $balance->remaining_days - $days,
                    ]);

                }

            }

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }
}
