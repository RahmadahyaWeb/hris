<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        try {
            return User::with('roles')
                ->latest()
                ->paginate($perPage);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function create(array $data): User
    {
        DB::beginTransaction();

        try {

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'branch_id' => $data['branch_id'],
                'position_id' => $data['position_id'],
            ]);

            if (! empty($data['roles'])) {
                $user->syncRoles($data['roles']);
            }

            DB::commit();

            return $user->load('roles');

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data): User
    {
        DB::beginTransaction();

        try {

            $user = User::findOrFail($id);

            $payload = [
                'name' => $data['name'],
                'email' => $data['email'],
                'branch_id' => $data['branch_id'],
                'position_id' => $data['position_id'],
            ];

            if (! empty($data['password'])) {
                $payload['password'] = $data['password'];
            }

            $user->update($payload);

            if (isset($data['roles'])) {
                $user->syncRoles($data['roles']);
            }

            DB::commit();

            return $user->load('roles');

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(int $id): void
    {
        DB::beginTransaction();

        try {

            $user = User::findOrFail($id);
            $user->delete();

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
