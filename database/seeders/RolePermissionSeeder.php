<?php

namespace Database\Seeders;

use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {

            $permissions = [

                'dashboard.view',

                'users.view',
                'users.create',
                'users.update',
                'users.delete',

                'roles.view',
                'roles.create',
                'roles.update',
                'roles.delete',

                'branches.view',
                'branches.create',
                'branches.update',
                'branches.delete',

                'divisions.view',
                'divisions.create',
                'divisions.update',
                'divisions.delete',

                'positions.view',
                'positions.create',
                'positions.update',
                'positions.delete',

                'user_devices.view',
                'user_devices.create',
                'user_devices.update',
                'user_devices.delete',

                'shifts.view',
                'shifts.create',
                'shifts.update',
                'shifts.delete',

                'work_calendars.view',
                'work_calendars.create',
                'work_calendars.update',
                'work_calendars.delete',

                'employee_schedules.view',
                'employee_schedules.create',
                'employee_schedules.update',
                'employee_schedules.delete',

                'attendance_rules.view',
                'attendance_rules.create',
                'attendance_rules.update',
                'attendance_rules.delete',

                'leaves.view',
                'leaves.create',
                'leaves.update',
                'leaves.delete',
                'leaves.approve',
            ];

            foreach ($permissions as $permission) {

                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => 'web',
                ]);

            }

            $superAdminRole = Role::firstOrCreate([
                'name' => 'super-admin',
                'guard_name' => 'web',
            ]);

            $superAdminRole->syncPermissions(Permission::all());

            $users = [

                [
                    'name' => 'Super Admin',
                    'email' => 'superadmin@example.com',
                    'role' => $superAdminRole,
                    'position' => 'Backend Developer',
                ],

            ];

            foreach ($users as $data) {

                $user = User::firstOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['name'],
                        'password' => Hash::make('password'),
                    ]
                );

                $user->assignRole($data['role']);

            }

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }
}
