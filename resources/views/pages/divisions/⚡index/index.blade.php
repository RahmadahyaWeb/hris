<div class="space-y-6">

    <div>
        <flux:heading size="lg">Division Management</flux:heading>
        <flux:text class="mt-2">
            Manage organizational divisions used to group employees.
        </flux:text>
    </div>

    <flux:card>

        <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <flux:heading size="lg">Division List</flux:heading>
                <flux:text class="mt-1 text-sm">
                    Create and manage divisions within the organization.
                </flux:text>
            </div>

            <div class="w-full sm:w-auto">

                @can('create', App\Models\Division::class)
                    <flux:button class="w-full sm:w-auto" icon="plus" wire:click="create">
                        Create Division
                    </flux:button>
                @endcan

            </div>

        </div>

        <flux:table :paginate="$this->divisions">

            <flux:table.columns>
                <flux:table.column>Division</flux:table.column>
                <flux:table.column>Positions</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>

                @forelse ($this->divisions as $division)
                    <flux:table.row>

                        <flux:table.cell>
                            {{ $division->name }}
                        </flux:table.cell>

                        <flux:table.cell>

                            <flux:badge color="zinc">
                                {{ $division->positions_count }} Positions
                            </flux:badge>

                        </flux:table.cell>

                        <flux:table.cell class="text-right">

                            <flux:dropdown>

                                <flux:button size="sm" icon="ellipsis-horizontal" />

                                <flux:menu>

                                    @can('update', $division)
                                        <flux:menu.item icon="pencil" wire:click="edit({{ $division->id }})">
                                            Edit
                                        </flux:menu.item>
                                    @endcan

                                    @can('delete', $division)
                                        <flux:menu.separator />

                                        <flux:menu.item icon="trash" variant="danger"
                                            wire:click="confirmDelete({{ $division->id }})">
                                            Delete
                                        </flux:menu.item>
                                    @endcan

                                </flux:menu>

                            </flux:dropdown>

                        </flux:table.cell>

                    </flux:table.row>

                @empty

                    <flux:table.row>

                        <flux:table.cell colspan="2">

                            <div class="flex flex-col items-center justify-center py-10">

                                <flux:heading size="sm">
                                    No Divisions Found
                                </flux:heading>

                                <flux:text class="mt-1 text-sm text-zinc-500">
                                    There are currently no divisions in the system.
                                </flux:text>

                            </div>

                        </flux:table.cell>

                    </flux:table.row>
                @endforelse

            </flux:table.rows>

        </flux:table>

    </flux:card>

    <flux:modal name="division-form" class="md:w-[28rem]">

        <div class="space-y-6">

            <div>

                <flux:heading size="lg">
                    {{ $divisionId ? 'Edit Division' : 'Create Division' }}
                </flux:heading>

                <flux:text class="mt-2">
                    Define a division used for grouping employees.
                </flux:text>

            </div>

            <flux:input label="Division Name" wire:model="name" placeholder="Division name" />

            <div class="flex">

                <flux:spacer />

                <flux:button variant="primary" wire:click="save">
                    Save
                </flux:button>

            </div>

        </div>

    </flux:modal>

    <x-delete-modal name="delete-division" heading="Delete Division?"
        message="You're about to delete this division.<br>This action cannot be reversed." action="destroy" />

</div>
