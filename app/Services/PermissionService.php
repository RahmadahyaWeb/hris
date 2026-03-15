<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class PermissionService
{
    public function getAllPermissions(): Collection
    {
        try {
            return Permission::all();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getPermissionById(int $id): Permission
    {
        try {
            return Permission::findOrFail($id);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function createPermission(string $name, string $guard = 'web'): Permission
    {
        DB::beginTransaction();

        try {
            $permission = Permission::create([
                'name' => $name,
                'guard_name' => $guard,
            ]);

            DB::commit();

            return $permission;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updatePermission(int $id, string $name): Permission
    {
        DB::beginTransaction();

        try {
            $permission = Permission::findOrFail($id);

            $permission->update([
                'name' => $name,
            ]);

            DB::commit();

            return $permission;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deletePermission(int $id): void
    {
        DB::beginTransaction();

        try {
            $permission = Permission::findOrFail($id);
            $permission->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
