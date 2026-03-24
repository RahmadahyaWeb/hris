<?php

namespace Database\Seeders;

use App\Models\ApprovalStep;
use App\Models\Branch;
use App\Models\Division;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Position;
use App\Models\Shift;
use App\Models\ShiftBreak;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EnterpriseDemoSeeder extends Seeder
{
    public function run(): void
    {
        try {

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

            $division = Division::firstOrCreate(['name' => 'Engineering']);

            $manager = Position::firstOrCreate([
                'division_id' => $division->id,
                'title' => 'Manager',
                'parent_id' => null,
            ]);

            $staff = Position::firstOrCreate([
                'division_id' => $division->id,
                'title' => 'Staff',
                'parent_id' => $manager->id,
            ]);

            /*
            |------------------------------------------------------------
            | USERS (3)
            |------------------------------------------------------------
            */

            $morningUser = User::firstOrCreate(
                ['email' => 'morning@example.com'],
                [
                    'name' => 'Morning User',
                    'password' => Hash::make('password'),
                    'branch_id' => $branch->id,
                    'position_id' => $staff->id,
                ]
            );

            $nightUser = User::firstOrCreate(
                ['email' => 'night@example.com'],
                [
                    'name' => 'Night User',
                    'password' => Hash::make('password'),
                    'branch_id' => $branch->id,
                    'position_id' => $staff->id,
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
            | CALENDAR (LIGHT)
            |------------------------------------------------------------
            */

            $start = date('Y-m-01');

            for ($i = 0; $i < 23; $i++) {

                $date = date('Y-m-d', strtotime("+$i days", strtotime($start)));

                DB::table('work_calendars')->insertOrIgnore([
                    'date' => $date,
                    'is_holiday' => date('N', strtotime($date)) >= 6,
                    'description' => date('N', strtotime($date)) >= 6 ? 'Weekend' : null,
                ]);
            }

            /*
            |------------------------------------------------------------
            | SCHEDULE + ATTENDANCE + BREAK (ULTRA LIGHT)
            |------------------------------------------------------------
            */

            for ($i = 0; $i < 23; $i++) {

                $date = date('Y-m-d', strtotime("+$i days", strtotime($start)));

                if (date('N', strtotime($date)) >= 6) {
                    continue;
                }

                /*
                |-------------------------
                | SCHEDULE
                |-------------------------
                */
                DB::table('employee_schedules')->insertOrIgnore([
                    [
                        'user_id' => $morningUser->id,
                        'date' => $date,
                        'shift_id' => $morningShift->id,
                    ],
                    [
                        'user_id' => $nightUser->id,
                        'date' => $date,
                        'shift_id' => $nightShift->id,
                    ],
                ]);

                /*
                |-------------------------
                | MORNING ATTENDANCE
                |-------------------------
                */

                $attendanceId = DB::table('attendances')->insertGetId([
                    'user_id' => $morningUser->id,
                    'date' => $date,
                    'checkin_at' => $date.' 08:05:00',
                    'checkout_at' => $date.' 16:30:00',
                    'state' => 'on_time',
                    'late_minutes' => 5,
                    'work_minutes' => 480,
                    'overtime_minutes' => 30,
                ]);

                DB::table('attendance_breaks')->insert([
                    'attendance_id' => $attendanceId,
                    'start_at' => $date.' 12:00:00',
                    'end_at' => $date.' 13:00:00',
                    'duration_minutes' => 60,
                ]);

                /*
                |-------------------------
                | NIGHT ATTENDANCE
                |-------------------------
                */

                $attendanceId = DB::table('attendances')->insertGetId([
                    'user_id' => $nightUser->id,
                    'date' => $date,
                    'checkin_at' => $date.' 22:05:00',
                    'checkout_at' => date('Y-m-d H:i:s', strtotime($date.' 04:45:00 +1 day')),
                    'state' => 'on_time',
                    'late_minutes' => 5,
                    'work_minutes' => 390,
                    'overtime_minutes' => 15,
                ]);

                DB::table('attendance_breaks')->insert([
                    'attendance_id' => $attendanceId,
                    'start_at' => date('Y-m-d H:i:s', strtotime($date.' 01:00:00 +1 day')),
                    'end_at' => date('Y-m-d H:i:s', strtotime($date.' 01:30:00 +1 day')),
                    'duration_minutes' => 30,
                ]);
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

            foreach ([$morningUser, $nightUser] as $user) {

                LeaveBalance::firstOrCreate([
                    'user_id' => $user->id,
                    'leave_type_id' => $leave->id,
                    'year' => date('Y'),
                ], [
                    'total_days' => 12,
                    'used_days' => 0,
                    'remaining_days' => 12,
                ]);
            }

        } catch (Exception $e) {
            throw $e;
        }
    }
}
