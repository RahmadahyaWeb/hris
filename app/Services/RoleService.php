<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function getAllRoles(): Collection
    {
        try {
            return Role::with('permissions')->get();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getRoleById(int $id): Role
    {
        try {
            return Role::with('permissions')->findOrFail($id);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function createRole(string $name, string $guard = 'web', array $permissions = []): Role
    {
        DB::beginTransaction();

        try {
            $role = Role::create([
                'name' => $name,
                'guard_name' => $guard,
            ]);

            if (! empty($permissions)) {
                $role->syncPermissions($permissions);
            }

            DB::commit();

            return $role->load('permissions');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateRole(int $id, string $name, array $permissions = []): Role
    {
        DB::beginTransaction();

        try {
            $role = Role::findOrFail($id);

            $role->update([
                'name' => $name,
            ]);

            $role->syncPermissions($permissions);

            DB::commit();

            return $role->load('permissions');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteRole(int $id): void
    {
        DB::beginTransaction();

        try {
            $role = Role::findOrFail($id);
            $role->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function assignPermissions(int $roleId, array $permissions): Role
    {
        DB::beginTransaction();

        try {
            $role = Role::findOrFail($roleId);
            $role->syncPermissions($permissions);

            DB::commit();

            return $role->load('permissions');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function givePermission(int $roleId, string $permission): Role
    {
        DB::beginTransaction();

        try {
            $role = Role::findOrFail($roleId);
            $role->givePermissionTo($permission);

            DB::commit();

            return $role->load('permissions');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function revokePermission(int $roleId, string $permission): Role
    {
        DB::beginTransaction();

        try {
            $role = Role::findOrFail($roleId);
            $role->revokePermissionTo($permission);

            DB::commit();

            return $role->load('permissions');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
