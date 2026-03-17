<div class="space-y-6">

    <div>
        <flux:heading size="lg">Branch Management</flux:heading>
        <flux:text class="mt-2">
            Manage company branches with geographic location and attendance radius.
        </flux:text>
    </div>

    <flux:card>

        <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <flux:heading size="lg">Branch List</flux:heading>
                <flux:text class="mt-1 text-sm">
                    Create and manage branch locations used for GPS attendance validation.
                </flux:text>
            </div>

            <div class="w-full sm:w-auto">
                @can('create', App\Models\Branch::class)
                    <flux:button class="w-full sm:w-auto" icon="plus" wire:click="create">
                        Create Branch
                    </flux:button>
                @endcan
            </div>

        </div>

        <flux:table :paginate="$this->branches">

            <flux:table.columns>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Location</flux:table.column>
                <flux:table.column>Radius</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>

                @forelse ($this->branches as $branch)
                    <flux:table.row>

                        <flux:table.cell>
                            {{ $branch->name }}
                        </flux:table.cell>

                        <flux:table.cell>

                            <div class="flex flex-col text-sm">

                                <span>
                                    Lat: {{ $branch->latitude }}
                                </span>

                                <span class="text-zinc-500">
                                    Lng: {{ $branch->longitude }}
                                </span>

                            </div>

                        </flux:table.cell>

                        <flux:table.cell>

                            <flux:badge color="zinc">
                                {{ $branch->radius }} m
                            </flux:badge>

                        </flux:table.cell>

                        <flux:table.cell class="text-right">

                            <flux:dropdown>

                                <flux:button size="sm" icon="ellipsis-horizontal" />

                                <flux:menu>

                                    @can('update', $branch)
                                        <flux:menu.item icon="pencil" wire:click="edit({{ $branch->id }})">
                                            Edit
                                        </flux:menu.item>
                                    @endcan

                                    @can('delete', $branch)
                                        <flux:menu.separator />

                                        <flux:menu.item icon="trash" variant="danger"
                                            wire:click="confirmDelete({{ $branch->id }})">
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
                                    No Branches Found
                                </flux:heading>

                                <flux:text class="mt-1 text-sm text-zinc-500">
                                    There are currently no branches in the system.
                                </flux:text>

                            </div>

                        </flux:table.cell>

                    </flux:table.row>
                @endforelse

            </flux:table.rows>

        </flux:table>

    </flux:card>

    <flux:modal name="branch-form" class="md:w-[30rem]">

        <div class="space-y-6">

            <div>

                <flux:heading size="lg">
                    {{ $branchId ? 'Edit Branch' : 'Create Branch' }}
                </flux:heading>

                <flux:text class="mt-2">
                    Configure branch location and attendance radius.
                </flux:text>

            </div>

            <flux:input label="Branch Name" wire:model="name" placeholder="Branch name" />

            <flux:input label="Latitude" wire:model="latitude" placeholder="-6.2000000" />

            <flux:input label="Longitude" wire:model="longitude" placeholder="106.8166667" />

            <flux:input label="Radius (meters)" type="number" wire:model="radius" />

            <div class="flex">
                <flux:spacer />

                <flux:button variant="primary" wire:click="save">
                    Save
                </flux:button>

            </div>

        </div>

    </flux:modal>

    <x-delete-modal name="delete-branch" heading="Delete Branch?"
        message="You're about to delete this branch.<br>This action cannot be reversed." action="destroy" />

</div>
