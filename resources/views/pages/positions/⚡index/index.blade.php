<div class="space-y-6">

    <div>
        <flux:heading size="lg">Position Management</flux:heading>
        <flux:text class="mt-2">
            Manage job positions and assign them to divisions.
        </flux:text>
    </div>

    <flux:card>

        <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <flux:heading size="lg">Position List</flux:heading>
                <flux:text class="mt-1 text-sm">
                    Create and manage positions within each division.
                </flux:text>
            </div>

            <div class="w-full sm:w-auto">

                @can('create', App\Models\Position::class)
                    <flux:button class="w-full sm:w-auto" icon="plus" wire:click="create">
                        Create Position
                    </flux:button>
                @endcan

            </div>

        </div>

        <flux:table :paginate="$this->positions">

            <flux:table.columns>
                <flux:table.column>title</flux:table.column>
                <flux:table.column>Division</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>

                @forelse ($this->positions as $position)
                    <flux:table.row>

                        <flux:table.cell>
                            {{ $position->title }}
                        </flux:table.cell>

                        <flux:table.cell>

                            <flux:badge color="zinc">
                                {{ $position->division->name }}
                            </flux:badge>

                        </flux:table.cell>

                        <flux:table.cell>
                            @if (is_null($position->parent_id))
                                <flux:badge color="green" size="sm">Parent</flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-right">

                            <flux:dropdown>

                                <flux:button size="sm" icon="ellipsis-horizontal" />

                                <flux:menu>

                                    @can('update', $position)
                                        <flux:menu.item icon="arrow-up" wire:click="setAsParent({{ $position->id }})">
                                            Set as Parent
                                        </flux:menu.item>

                                        <flux:menu.item icon="pencil" wire:click="edit({{ $position->id }})">
                                            Edit
                                        </flux:menu.item>
                                    @endcan

                                    @can('delete', $position)
                                        <flux:menu.separator />

                                        <flux:menu.item icon="trash" variant="danger"
                                            wire:click="confirmDelete({{ $position->id }})">
                                            Delete
                                        </flux:menu.item>
                                    @endcan

                                </flux:menu>

                            </flux:dropdown>

                        </flux:table.cell>

                    </flux:table.row>

                @empty

                    <flux:table.row>

                        <flux:table.cell colspan="4">

                            <div class="flex flex-col items-center justify-center py-10">

                                <flux:heading size="sm">
                                    No Positions Found
                                </flux:heading>

                                <flux:text class="mt-1 text-sm text-zinc-500">
                                    There are currently no positions in the system.
                                </flux:text>

                            </div>

                        </flux:table.cell>

                    </flux:table.row>
                @endforelse

            </flux:table.rows>

        </flux:table>

    </flux:card>

    <flux:modal name="position-form" class="md:w-[30rem]">

        <div class="space-y-6">

            <div>

                <flux:heading size="lg">
                    {{ $positionId ? 'Edit Position' : 'Create Position' }}
                </flux:heading>

                <flux:text class="mt-2">
                    Define a job position and assign it to a division.
                </flux:text>

            </div>

            <flux:input label="Position Name" wire:model="name" placeholder="Position name" />

            <flux:select label="Division" wire:model="division_id">

                <option value="">Select division</option>

                @foreach ($this->divisions as $id => $division)
                    <option value="{{ $id }}">
                        {{ $division }}
                    </option>
                @endforeach

            </flux:select>

            <div class="flex">

                <flux:spacer />

                <flux:button variant="primary" wire:click="save">
                    Save
                </flux:button>

            </div>

        </div>

    </flux:modal>

    <x-delete-modal name="delete-position" heading="Delete Position?"
        message="You're about to delete this position.<br>This action cannot be reversed." action="destroy" />

</div>
