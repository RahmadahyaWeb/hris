<?php

use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

new class extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public ?int $userId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public array $roles = [];

    public ?int $deleteId = null;

    #[Computed]
    public function users()
    {
        $this->authorize('viewAny', User::class);

        $userService = new UserService;

        return $userService->paginate($this->perPage);
    }

    #[Computed]
    public function availableRoles(): array
    {
        return Role::pluck('name')->toArray();
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$this->userId],
            'password' => [$this->userId ? 'nullable' : 'required', 'string', 'min:6'],
            'roles' => ['required', 'array'],
        ];
    }

    public function create(): void
    {
        $this->authorize('create', User::class);

        $this->reset(['userId', 'name', 'email', 'password', 'roles']);

        $this->modal('user-form')->show();
    }

    public function edit(int $id): void
    {
        $user = User::with('roles')->findOrFail($id);

        $this->authorize('update', $user);

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->roles = $user->roles->pluck('name')->toArray();
        $this->password = '';

        $this->modal('user-form')->show();
    }

    public function save(): void
    {
        $userService = new UserService;

        $validated = $this->validate();

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'roles' => $validated['roles'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        if ($this->userId) {

            $user = User::findOrFail($this->userId);

            $this->authorize('update', $user);

            $userService->update($this->userId, $data);

            $message = 'User updated successfully';

        } else {

            $this->authorize('create', User::class);

            $data['password'] = Hash::make($validated['password']);

            $userService->create($data);

            $message = 'User created successfully';
        }

        $this->modal('user-form')->close();

        $this->dispatch('alert', [
            'title' => 'Success',
            'message' => $message,
            'variant' => 'success',
        ]);

        $this->reset(['userId', 'name', 'email', 'password', 'roles']);
    }

    public function confirmDelete(int $id): void
    {
        $user = User::findOrFail($id);

        $this->authorize('delete', $user);

        $this->deleteId = $id;

        $this->modal('delete-user')->show();
    }

    public function destroy(): void
    {
        $userService = new UserService;

        $user = User::findOrFail($this->deleteId);

        $this->authorize('delete', $user);

        $userService->delete($this->deleteId);

        $this->modal('delete-user')->close();

        $this->dispatch('alert', [
            'title' => 'Success',
            'message' => 'User deleted successfully',
            'variant' => 'success',
        ]);

        $this->deleteId = null;
    }
};
