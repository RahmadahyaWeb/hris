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

                'users.view',
                'users.create',
                'users.update',

                'roles.view',
                'roles.create',
                'roles.update',
            ]);

            $userRole->syncPermissions([
                'dashboard.view',
            ]);

            $superAdmin = User::firstOrCreate(
                [
                    'email' => 'superadmin@example.com',
                ],
                [
                    'name' => 'Super Admin',
                    'password' => Hash::make('password'),
                ]
            );

            $admin = User::firstOrCreate(
                [
                    'email' => 'admin@example.com',
                ],
                [
                    'name' => 'Admin',
                    'password' => Hash::make('password'),
                ]
            );

            $user = User::firstOrCreate(
                [
                    'email' => 'user@example.com',
                ],
                [
                    'name' => 'User',
                    'password' => Hash::make('password'),
                ]
            );

            $superAdmin->assignRole($superAdminRole);
            $admin->assignRole($adminRole);
            $user->assignRole($userRole);

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
