<?php

namespace Database\Seeders;

use App\Models\ApprovalStep;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Division;
use App\Models\EmployeeSchedule;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Position;
use App\Models\Shift;
use App\Models\User;
use App\Models\WorkCalendar;
use App\Services\AttendanceStateService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EnterpriseDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {

            $branch = Branch::firstOrCreate(
                ['name' => 'Head Office'],
                [
                    'latitude' => -3.3186000,
                    'longitude' => 114.5944000,
                    'radius' => 100,
                ]
            );

            $engineering = Division::firstOrCreate(['name' => 'Engineering']);
            $hr = Division::firstOrCreate(['name' => 'Human Resources']);
            $sales = Division::firstOrCreate(['name' => 'Sales']);

            $engineeringManager = Position::firstOrCreate([
                'division_id' => $engineering->id,
                'title' => 'Engineering Manager',
                'parent_id' => null,
            ]);

            $backend = Position::firstOrCreate([
                'division_id' => $engineering->id,
                'title' => 'Backend Developer',
            ], [
                'parent_id' => $engineeringManager->id,
            ]);

            $frontend = Position::firstOrCreate([
                'division_id' => $engineering->id,
                'title' => 'Frontend Developer',
            ], [
                'parent_id' => $engineeringManager->id,
            ]);

            $devops = Position::firstOrCreate([
                'division_id' => $engineering->id,
                'title' => 'DevOps Engineer',
            ], [
                'parent_id' => $engineeringManager->id,
            ]);

            $hrManager = Position::firstOrCreate([
                'division_id' => $hr->id,
                'title' => 'HR Manager',
                'parent_id' => null,
            ]);

            $salesExecutive = Position::firstOrCreate([
                'division_id' => $sales->id,
                'title' => 'Sales Executive',
                'parent_id' => null,
            ]);

            $engineeringManagerUser = User::firstOrCreate(
                ['email' => 'engmanager@example.com'],
                [
                    'name' => 'Engineering Manager',
                    'password' => Hash::make('password'),
                    'branch_id' => $branch->id,
                    'position_id' => $engineeringManager->id,
                ]
            );

            $backendUser = User::firstOrCreate(
                ['email' => 'backend@example.com'],
                [
                    'name' => 'Backend Dev',
                    'password' => Hash::make('password'),
                    'branch_id' => $branch->id,
                    'position_id' => $backend->id,
                ]
            );

            $frontendUser = User::firstOrCreate(
                ['email' => 'frontend@example.com'],
                [
                    'name' => 'Frontend Dev',
                    'password' => Hash::make('password'),
                    'branch_id' => $branch->id,
                    'position_id' => $frontend->id,
                ]
            );

            $devopsUser = User::firstOrCreate(
                ['email' => 'devops@example.com'],
                [
                    'name' => 'DevOps Engineer',
                    'password' => Hash::make('password'),
                    'branch_id' => $branch->id,
                    'position_id' => $devops->id,
                ]
            );

            $hrUser = User::firstOrCreate(
                ['email' => 'hr@example.com'],
                [
                    'name' => 'HR Manager',
                    'password' => Hash::make('password'),
                    'branch_id' => $branch->id,
                    'position_id' => $hrManager->id,
                ]
            );

            $salesUser = User::firstOrCreate(
                ['email' => 'sales@example.com'],
                [
                    'name' => 'Sales Executive',
                    'password' => Hash::make('password'),
                    'branch_id' => $branch->id,
                    'position_id' => $salesExecutive->id,
                ]
            );

            $users = [
                $engineeringManagerUser,
                $backendUser,
                $frontendUser,
                $devopsUser,
                $hrUser,
                $salesUser,
            ];

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

            $start = Carbon::create(now()->year, 2, 1);
            $end = Carbon::create(2026, 3, 23);

            $date = $start->copy();

            while ($date->lte($end)) {

                WorkCalendar::firstOrCreate(
                    ['date' => $date->toDateString()],
                    [
                        'is_holiday' => $date->isWeekend(),
                        'description' => $date->isWeekend() ? 'Weekend' : null,
                    ]
                );

                $date->addDay();
            }

            $date = $start->copy();

            while ($date->lte($end)) {

                if (! $date->isWeekend()) {

                    foreach ($users as $user) {

                        $shiftId = $user->id === $backendUser->id
                            ? $nightShift->id
                            : $morningShift->id;

                        EmployeeSchedule::firstOrCreate(
                            [
                                'user_id' => $user->id,
                                'date' => $date->toDateString(),
                            ],
                            [
                                'shift_id' => $shiftId,
                            ]
                        );
                    }
                }

                $date->addDay();
            }

            $schedules = EmployeeSchedule::with('shift')->get();

            foreach ($schedules as $schedule) {

                if (rand(1, 100) <= 5) {
                    continue;
                }

                $date = Carbon::parse($schedule->date);

                $shiftStart = $date->copy()
                    ->setTimeFromTimeString($schedule->shift->start_time);

                $shiftEnd = $date->copy()
                    ->setTimeFromTimeString($schedule->shift->end_time);

                if ($schedule->shift->cross_midnight && $shiftEnd->lte($shiftStart)) {
                    $shiftEnd->addDay();
                }

                $checkin = $shiftStart->copy()->addMinutes(rand(-5, 40));
                $checkout = $shiftEnd->copy()->addMinutes(rand(-10, 120));

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
                    'late_minutes' => $state['late_minutes'],
                    'work_minutes' => $state['work_minutes'],
                    'overtime_minutes' => $state['overtime_minutes'],
                ]);
            }

            $annual = LeaveType::firstOrCreate(['name' => 'Annual Leave']);

            ApprovalStep::firstOrCreate([
                'leave_type_id' => $annual->id,
                'step_order' => 1,
            ], [
                'approver_type' => 'manager',
            ]);

            ApprovalStep::firstOrCreate([
                'leave_type_id' => $annual->id,
                'step_order' => 2,
            ], [
                'approver_type' => 'hr',
            ]);

            foreach ($users as $user) {

                LeaveBalance::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'leave_type_id' => $annual->id,
                        'year' => now()->year,
                    ],
                    [
                        'total_days' => 12,
                        'used_days' => 0,
                        'remaining_days' => 12,
                    ]
                );
            }

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();
            throw $e;
        }
    }
}
