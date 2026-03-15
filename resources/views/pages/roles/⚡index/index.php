<?php

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

new class extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public ?int $roleId = null;

    public string $name = '';

    public array $permissions = [];

    public ?int $deleteId = null;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$this->roleId],
            'permissions' => ['array'],
        ];
    }

    #[Computed]
    public function roles()
    {
        $this->authorize('viewAny', Role::class);

        return Role::with('permissions')
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function groupedPermissions(): array
    {
        $permissions = Permission::pluck('name')->toArray();

        $groups = [];

        foreach ($permissions as $permission) {

            [$group,$action] = explode('.', $permission);

            $groups[$group][] = $permission;
        }

        return $groups;
    }

    public function create(): void
    {
        $this->authorize('create', Role::class);

        $this->reset(['roleId', 'name', 'permissions']);

        $this->modal('role-form')->show();
    }

    public function edit(int $id): void
    {
        $role = Role::with('permissions')->findOrFail($id);

        $this->authorize('update', $role);

        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->permissions = $role->permissions->pluck('name')->toArray();

        $this->modal('role-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate();

        DB::beginTransaction();

        try {

            if ($this->roleId) {

                $role = Role::findOrFail($this->roleId);

                $this->authorize('update', $role);

                $role->update([
                    'name' => $validated['name'],
                ]);

                $role->syncPermissions($validated['permissions'] ?? []);

                $message = 'Role updated successfully';

            } else {

                $this->authorize('create', Role::class);

                $role = Role::create([
                    'name' => $validated['name'],
                    'guard_name' => 'web',
                ]);

                $role->syncPermissions($validated['permissions'] ?? []);

                $message = 'Role created successfully';
            }

            DB::commit();

            $this->modal('role-form')->close();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => $message,
                'variant' => 'success',
            ]);

            $this->reset(['roleId', 'name', 'permissions']);

        } catch (Throwable $e) {

            DB::rollBack();

            throw $e;
        }
    }

    public function confirmDelete(int $id): void
    {
        $role = Role::findOrFail($id);

        $this->authorize('delete', $role);

        $this->deleteId = $id;

        $this->modal('delete-role')->show();
    }

    public function destroy(): void
    {
        DB::beginTransaction();

        try {

            $role = Role::findOrFail($this->deleteId);

            $this->authorize('delete', $role);

            $role->delete();

            DB::commit();

            $this->modal('delete-role')->close();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => 'Role deleted successfully',
                'variant' => 'success',
            ]);

            $this->deleteId = null;

        } catch (Throwable $e) {

            DB::rollBack();

            throw $e;
        }
    }
};
