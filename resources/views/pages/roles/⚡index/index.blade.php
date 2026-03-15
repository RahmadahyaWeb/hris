<div class="space-y-6">

    <div>
        <flux:heading>Role Management</flux:heading>
        <flux:text class="mt-2">
            Manage roles and assign permissions that control access across the system.
        </flux:text>
    </div>

    <flux:card>

        <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <flux:heading size="lg">Role List</flux:heading>
                <flux:text class="mt-1 text-sm">
                    Create and manage system roles with permission assignments.
                </flux:text>
            </div>

            <div class="w-full sm:w-auto">
                <flux:button class="w-full sm:w-auto" icon="plus" wire:click="create">
                    Create Role
                </flux:button>
            </div>

        </div>

        <flux:table :paginate="$this->roles">

            <flux:table.columns>
                <flux:table.column>Role</flux:table.column>
                <flux:table.column>Permissions</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>

                @forelse ($this->roles as $role)

                    <flux:table.row>

                        <flux:table.cell>
                            {{ $role->name }}
                        </flux:table.cell>

                        <flux:table.cell>

                            @php
                                $visible = $role->permissions->take(2);
                                $remaining = $role->permissions->count() - $visible->count();
                            @endphp

                            <div class="flex flex-wrap gap-2">

                                @foreach ($visible as $permission)
                                    <flux:badge color="zinc">
                                        {{ $permission->name }}
                                    </flux:badge>
                                @endforeach

                                @if ($remaining > 0)
                                    <flux:badge color="gray">
                                        +{{ $remaining }}
                                    </flux:badge>
                                @endif

                            </div>

                        </flux:table.cell>

                        <flux:table.cell class="text-right">

                            <flux:dropdown>

                                <flux:button size="sm" icon="ellipsis-horizontal" />

                                <flux:menu>

                                    <flux:menu.item icon="pencil" wire:click="edit({{ $role->id }})">
                                        Edit
                                    </flux:menu.item>

                                    <flux:menu.item icon="trash" variant="danger"
                                        wire:click="confirmDelete({{ $role->id }})">
                                        Delete
                                    </flux:menu.item>

                                </flux:menu>

                            </flux:dropdown>

                        </flux:table.cell>

                    </flux:table.row>

                @empty

                    <flux:table.row>

                        <flux:table.cell colspan="3">

                            <div class="flex flex-col items-center justify-center py-10">

                                <flux:heading size="sm">
                                    No Roles Found
                                </flux:heading>

                                <flux:text class="mt-1 text-sm text-zinc-500">
                                    There are currently no roles in the system.
                                </flux:text>

                            </div>

                        </flux:table.cell>

                    </flux:table.row>

                @endforelse

            </flux:table.rows>

        </flux:table>

    </flux:card>

    <flux:modal name="role-form" class="md:w-[40rem]">

        <div class="space-y-6">

            <div>
                <flux:heading size="lg">
                    {{ $roleId ? 'Edit Role' : 'Create Role' }}
                </flux:heading>

                <flux:text class="mt-2">
                    Assign permissions to define what actions this role can perform.
                </flux:text>
            </div>

            <flux:input label="Role Name" wire:model="name" placeholder="Role name" />

            <div class="space-y-6">

                @foreach ($this->groupedPermissions as $group => $permissions)
                    <flux:checkbox.group wire:model="permissions" label="{{ ucfirst($group) }}">

                        @foreach ($permissions as $permission)
                            <flux:checkbox value="{{ $permission }}" label="{{ explode('.', $permission)[1] }}" />
                        @endforeach

                    </flux:checkbox.group>
                @endforeach

            </div>

            <div class="flex">
                <flux:spacer />

                <flux:button variant="primary" wire:click="save">
                    Save Role
                </flux:button>

            </div>

        </div>

    </flux:modal>

    <x-delete-modal name="delete-role" heading="Delete Role?"
        message="You're about to delete this role.<br>This action cannot be reversed." action="destroy" />

</div>
