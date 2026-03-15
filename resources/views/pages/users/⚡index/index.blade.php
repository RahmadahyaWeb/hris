<div class="space-y-6">

    <div>
        <flux:heading>User Management</flux:heading>
        <flux:text class="mt-2">
            Manage application users, including their account information and assigned roles.
        </flux:text>
    </div>

    <flux:card>

        <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <flux:heading size="lg">User List</flux:heading>
                <flux:text class="mt-1 text-sm">
                    View, create, update, and manage user access within the system.
                </flux:text>
            </div>

            <div class="w-full sm:w-auto">
                <flux:button class="w-full sm:w-auto" icon="plus" wire:click="create">
                    Create User
                </flux:button>
            </div>

        </div>

        <flux:table :paginate="$this->users">

            <flux:table.columns>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column>Roles</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>

                @forelse ($this->users as $user)

                    <flux:table.row>

                        <flux:table.cell>
                            {{ $user->name }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="text-sm">
                                {{ $user->email }}
                            </span>
                        </flux:table.cell>

                        <flux:table.cell>

                            <div class="flex flex-wrap gap-2">

                                @foreach ($user->roles as $role)
                                    <flux:badge color="zinc">
                                        {{ $role->name }}
                                    </flux:badge>
                                @endforeach

                                @if ($user->roles->isEmpty())
                                    <flux:badge color="red">
                                        No Role
                                    </flux:badge>
                                @endif

                            </div>

                        </flux:table.cell>

                        <flux:table.cell class="text-right">

                            <flux:dropdown>

                                <flux:button size="sm" icon="ellipsis-horizontal" />

                                <flux:menu>

                                    <flux:menu.item icon="pencil" wire:click="edit({{ $user->id }})">
                                        Edit
                                    </flux:menu.item>

                                    <flux:menu.separator />

                                    <flux:menu.item icon="trash" variant="danger"
                                        wire:click="confirmDelete({{ $user->id }})">
                                        Delete
                                    </flux:menu.item>

                                </flux:menu>

                            </flux:dropdown>

                        </flux:table.cell>

                    </flux:table.row>

                @empty

                    <flux:table.row>

                        <flux:table.cell colspan="4">

                            <div class="flex flex-col items-center justify-center py-10">

                                <flux:heading size="sm">
                                    No Users Found
                                </flux:heading>

                                <flux:text class="mt-1 text-sm text-zinc-500">
                                    There are currently no users in the system.
                                </flux:text>

                            </div>

                        </flux:table.cell>

                    </flux:table.row>

                @endforelse

            </flux:table.rows>

        </flux:table>

    </flux:card>

    <flux:modal name="user-form" class="md:w-96">

        <div class="space-y-6">

            <div>
                <flux:heading size="lg">
                    {{ $userId ? 'Edit User' : 'Create User' }}
                </flux:heading>

                <flux:text class="mt-2">
                    Manage user account information and role assignment.
                </flux:text>
            </div>

            <flux:input label="Name" wire:model="name" placeholder="User name" />

            <flux:input label="Email" type="email" wire:model="email" placeholder="user@email.com" />

            <flux:input label="Password" type="password" wire:model="password"
                placeholder="Leave empty when not changing" />

            <flux:checkbox.group wire:model="roles" label="Roles">

                @foreach ($this->availableRoles as $role)
                    <flux:checkbox label="{{ ucfirst($role) }}" value="{{ $role }}" />
                @endforeach

            </flux:checkbox.group>

            <div class="flex">
                <flux:spacer />

                <flux:button variant="primary" wire:click="save">
                    Save
                </flux:button>

            </div>

        </div>

    </flux:modal>

    <x-delete-modal name="delete-user" heading="Delete User?"
        message="You're about to delete this user.<br>This action cannot be reversed." action="destroy" />

</div>
