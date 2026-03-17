<div class="space-y-6">

    <div>
        <flux:heading size="lg">Shift Management</flux:heading>
        <flux:text class="mt-2">
            Manage working shifts including start time, end time, and overnight shifts.
        </flux:text>
    </div>

    <flux:card>

        <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <flux:heading size="lg">Shift List</flux:heading>
                <flux:text class="mt-1 text-sm">
                    Create and manage employee work shifts.
                </flux:text>
            </div>

            @can('create', App\Models\Shift::class)
                <div class="w-full sm:w-auto">

                    <flux:button class="w-full sm:w-auto" icon="plus" wire:click="create">

                        Create Shift

                    </flux:button>

                </div>
            @endcan

        </div>

        <flux:table :paginate="$this->shifts">

            <flux:table.columns>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Working Hours</flux:table.column>
                <flux:table.column>Type</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>

                @forelse ($this->shifts as $shift)
                    <flux:table.row>

                        <flux:table.cell>
                            {{ $shift->name }}
                        </flux:table.cell>

                        <flux:table.cell>

                            <div class="text-sm">

                                {{ $shift->start_time }} - {{ $shift->end_time }}

                            </div>

                        </flux:table.cell>

                        <flux:table.cell>

                            @if ($shift->cross_midnight)
                                <flux:badge color="yellow">
                                    Overnight
                                </flux:badge>
                            @else
                                <flux:badge color="zinc">
                                    Normal
                                </flux:badge>
                            @endif

                        </flux:table.cell>

                        <flux:table.cell class="text-right">

                            <flux:dropdown>

                                <flux:button size="sm" icon="ellipsis-horizontal" />

                                <flux:menu>

                                    @can('update', $shift)
                                        <flux:menu.item icon="pencil" wire:click="edit({{ $shift->id }})">

                                            Edit

                                        </flux:menu.item>
                                    @endcan

                                    @can('delete', $shift)
                                        <flux:menu.separator />

                                        <flux:menu.item icon="trash" variant="danger"
                                            wire:click="confirmDelete({{ $shift->id }})">

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
                                    No Shifts Found
                                </flux:heading>

                                <flux:text class="mt-1 text-sm text-zinc-500">
                                    There are currently no shifts configured.
                                </flux:text>

                            </div>

                        </flux:table.cell>

                    </flux:table.row>
                @endforelse

            </flux:table.rows>

        </flux:table>

    </flux:card>

    <flux:modal name="shift-form" class="md:w-[28rem]">

        <div class="space-y-6">

            <div>

                <flux:heading size="lg">
                    {{ $shiftId ? 'Edit Shift' : 'Create Shift' }}
                </flux:heading>

                <flux:text class="mt-2">
                    Define working hours for this shift.
                </flux:text>

            </div>

            <flux:input label="Shift Name" wire:model="name" placeholder="Morning Shift" />

            <flux:input type="time" label="Start Time" wire:model="start_time" />

            <flux:input type="time" label="End Time" wire:model="end_time" />

            <flux:checkbox label="Cross Midnight (Overnight Shift)" wire:model="cross_midnight" />

            <div class="flex">

                <flux:spacer />

                <flux:button variant="primary" wire:click="save">

                    Save

                </flux:button>

            </div>

        </div>

    </flux:modal>

    <x-delete-modal name="delete-shift" heading="Delete Shift?"
        message="You're about to delete this shift.<br>This action cannot be reversed." action="destroy" />

</div>
