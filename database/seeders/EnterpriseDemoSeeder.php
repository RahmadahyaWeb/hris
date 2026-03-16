<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Division;
use App\Models\EmployeeSchedule;
use App\Models\Leave;
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
            | Users (5)
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
            | Attendance
            |------------------------------------------------------------
            */

            $schedules = EmployeeSchedule::with('shift')
                ->whereBetween('date', [$start, $end])
                ->get();

            foreach ($schedules as $schedule) {

                $date = Carbon::parse($schedule->date)->startOfDay();

                $shiftStart = $date->copy()
                    ->setTimeFromTimeString($schedule->shift->start_time);

                $shiftEnd = $date->copy()
                    ->setTimeFromTimeString($schedule->shift->end_time);

                $checkin = $shiftStart->copy()->addMinutes(rand(-10, 25));

                $checkout = $shiftEnd->copy()->addMinutes(rand(-15, 60));

                $attendance = Attendance::create([

                    'user_id' => $schedule->user_id,

                    'date' => $schedule->date,

                    'checkin_at' => $checkin,

                    'checkout_at' => $checkout,

                ]);

                $stateService = new AttendanceStateService;

                $state = $stateService->resolve($attendance, $schedule);

                $lateMinutes = $checkin->greaterThan($shiftStart)
                    ? $shiftStart->diffInMinutes($checkin)
                    : 0;

                $workMinutes = $checkin->diffInMinutes($checkout);

                $overtimeMinutes = $checkout->greaterThan($shiftEnd)
                    ? $shiftEnd->diffInMinutes($checkout)
                    : 0;

                $attendance->update([

                    'state' => $state['state'],

                    'late_minutes' => $lateMinutes,

                    'work_minutes' => $workMinutes,

                    'overtime_minutes' => $overtimeMinutes,

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
            | Leaves
            |------------------------------------------------------------
            */

            foreach ($users as $user) {

                Leave::create([

                    'user_id' => $user->id,

                    'leave_type_id' => $annual->id,

                    'start_date' => now()->subDays(rand(10, 20))->toDateString(),

                    'end_date' => now()->subDays(rand(5, 9))->toDateString(),

                    'days' => rand(1, 3),

                    'reason' => 'Personal leave',

                    'status' => 'approved',

                    'approved_by' => $users[3]->id,

                    'approved_at' => now(),

                ]);

            }

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }
}
