<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Position;
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

            $adminRole = Role::firstOrCreate([
                'name' => 'admin',
                'guard_name' => 'web',
            ]);

            $userRole = Role::firstOrCreate([
                'name' => 'user',
                'guard_name' => 'web',
            ]);

            $superAdminRole->syncPermissions(Permission::all());

            $adminRole->syncPermissions([
                'dashboard.view',
            ]);

            $userRole->syncPermissions([
                'dashboard.view',
            ]);

            $branch = Branch::first();

            $positions = Position::with('division')->get();

            $users = [

                [
                    'name' => 'Super Admin',
                    'email' => 'superadmin@example.com',
                    'role' => $superAdminRole,
                    'position' => 'Backend Developer',
                ],

                [
                    'name' => 'System Admin',
                    'email' => 'admin@example.com',
                    'role' => $adminRole,
                    'position' => 'HR Manager',
                ],

                [
                    'name' => 'Backend Dev',
                    'email' => 'backend@example.com',
                    'role' => $userRole,
                    'position' => 'Backend Developer',
                ],

                [
                    'name' => 'Frontend Dev',
                    'email' => 'frontend@example.com',
                    'role' => $userRole,
                    'position' => 'Frontend Developer',
                ],

                [
                    'name' => 'DevOps',
                    'email' => 'devops@example.com',
                    'role' => $userRole,
                    'position' => 'DevOps Engineer',
                ],

                [
                    'name' => 'QA Engineer',
                    'email' => 'qa@example.com',
                    'role' => $userRole,
                    'position' => 'QA Engineer',
                ],

                [
                    'name' => 'HR Recruiter',
                    'email' => 'recruiter@example.com',
                    'role' => $userRole,
                    'position' => 'Recruiter',
                ],

                [
                    'name' => 'Finance Staff',
                    'email' => 'finance@example.com',
                    'role' => $userRole,
                    'position' => 'Finance Staff',
                ],

                [
                    'name' => 'Sales Manager',
                    'email' => 'salesmanager@example.com',
                    'role' => $userRole,
                    'position' => 'Sales Manager',
                ],

                [
                    'name' => 'Sales Executive',
                    'email' => 'sales@example.com',
                    'role' => $userRole,
                    'position' => 'Sales Executive',
                ],

            ];

            foreach ($users as $data) {

                $position = $positions->firstWhere('title', $data['position']);

                $user = User::firstOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['name'],
                        'password' => Hash::make('password'),
                        'branch_id' => $branch?->id,
                        'position_id' => $position?->id,
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
