<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {

            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            // ========================
            // PERMISSIONS
            // ========================
            $permissions = [
                'user.view',
                'user.create',
                'user.update',
                'user.delete',

                'role.view',
                'role.create',
                'role.update',
                'role.delete',

                'branch.view',
                'branch.create',
                'branch.update',
                'branch.delete',

                'department.view',
                'department.create',
                'department.update',
                'department.delete',

                'position.view',
                'position.create',
                'position.update',
                'position.delete',

                'team.view',
                'team.create',
                'team.update',
                'team.delete',

                'employee-assignment.view',
                'employee-assignment.create',
                'employee-assignment.update',
                'employee-assignment.delete',

                'shift.view',
                'shift.create',
                'shift.update',
                'shift.delete',

                'work-schedule.view',
                'work-schedule.create',
                'work-schedule.update',
                'work-schedule.delete',

                'employee-schedule.view',
                'employee-schedule.create',
                'employee-schedule.update',
                'employee-schedule.delete',

                'holiday.view',
                'holiday.create',
                'holiday.update',
                'holiday.delete',

                'leave.view',
                'leave.create',
                'leave.update',
                'leave.delete',
            ];

            foreach ($permissions as $perm) {
                Permission::firstOrCreate(['name' => $perm]);
            }

            // ========================
            // ROLES
            // ========================
            $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);

            // Super Admin → semua permission
            $superAdminRole->syncPermissions(Permission::all());

            // ========================
            // USERS
            // ========================
            $superAdmin = User::updateOrCreate(
                ['email' => 'superadmin@mail.com'],
                [
                    'name' => 'Super Admin',
                    'password' => Hash::make('password'),
                ]
            );

            // ========================
            // ASSIGN ROLE
            // ========================
            $superAdmin->syncRoles([$superAdminRole]);

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
