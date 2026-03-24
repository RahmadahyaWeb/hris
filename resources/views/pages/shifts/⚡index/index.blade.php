<div class="space-y-6">

    <div>
        <flux:heading size="lg">Shift Management</flux:heading>
        <flux:text class="mt-1 text-sm text-zinc-500">
            Manage working shifts and define break times per shift.
        </flux:text>
    </div>

    <flux:card>

        <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <flux:heading size="lg">Shift List</flux:heading>
                <flux:text class="mt-1 text-sm text-zinc-500">
                    Configure working hours and break policies.
                </flux:text>
            </div>

            <flux:button class="w-full sm:w-auto" icon="plus" wire:click="create">
                Create Shift
            </flux:button>

        </div>

        <flux:table :paginate="$this->shifts">

            <flux:table.columns>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Working Hours</flux:table.column>
                <flux:table.column>Break</flux:table.column>
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
                            @if ($shift->breaks->count())
                                <div class="space-y-1">
                                    @foreach ($shift->breaks as $b)
                                        <div class="text-xs text-zinc-600">
                                            {{ $b->start_time }} - {{ $b->end_time }}
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-zinc-400">No Break</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($shift->cross_midnight)
                                <flux:badge color="yellow">Overnight</flux:badge>
                            @else
                                <flux:badge color="zinc">Normal</flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-right">

                            <flux:dropdown>

                                <flux:button size="sm" icon="ellipsis-horizontal" />

                                <flux:menu>

                                    <flux:menu.item icon="pencil" wire:click="edit({{ $shift->id }})">
                                        Edit
                                    </flux:menu.item>

                                    <flux:menu.separator />

                                    <flux:menu.item icon="trash" variant="danger"
                                        wire:click="confirmDelete({{ $shift->id }})">
                                        Delete
                                    </flux:menu.item>

                                </flux:menu>

                            </flux:dropdown>

                        </flux:table.cell>

                    </flux:table.row>

                @empty

                    <flux:table.row>
                        <flux:table.cell colspan="5">

                            <div class="flex flex-col items-center justify-center py-10">

                                <flux:heading size="sm">
                                    No Shifts Found
                                </flux:heading>

                                <flux:text class="mt-1 text-sm text-zinc-500">
                                    Create your first shift to get started.
                                </flux:text>

                            </div>

                        </flux:table.cell>
                    </flux:table.row>

                @endforelse

            </flux:table.rows>

        </flux:table>

    </flux:card>

    {{-- MODAL FORM --}}
    <flux:modal name="shift-form" class="md:w-[32rem]">

        <div class="space-y-6">

            <div>
                <flux:heading size="lg">
                    {{ $shiftId ? 'Edit Shift' : 'Create Shift' }}
                </flux:heading>

                <flux:text class="mt-1 text-sm text-zinc-500">
                    Define working hours and break configuration.
                </flux:text>
            </div>

            <flux:input label="Shift Name" wire:model="name" placeholder="Morning Shift" />

            <div class="grid grid-cols-2 gap-3">

                <flux:input type="time" label="Start Time" wire:model="start_time" />

                <flux:input type="time" label="End Time" wire:model="end_time" />

            </div>

            <flux:checkbox label="Overnight Shift (Cross Midnight)" wire:model="cross_midnight" />

            {{-- BREAK SECTION --}}
            <flux:card>

                <div class="flex items-center justify-between">

                    <flux:heading size="sm">Break Time</flux:heading>

                    <flux:button size="xs" wire:click="addBreak">
                        Add Break
                    </flux:button>

                </div>

                <div class="mt-3 space-y-3">

                    @forelse ($breaks as $index => $break)
                        <div class="grid grid-cols-2 gap-2 items-end">

                            <flux:input type="time" label="Start"
                                wire:model="breaks.{{ $index }}.start_time" />

                            <flux:input type="time" label="End"
                                wire:model="breaks.{{ $index }}.end_time" />

                            <div class="col-span-2 text-right">

                                <flux:button size="xs" variant="danger"
                                    wire:click="removeBreak({{ $index }})">

                                    Remove

                                </flux:button>

                            </div>

                        </div>

                    @empty

                        <div class="text-sm text-zinc-400 text-center py-4">
                            No break configured
                        </div>
                    @endforelse

                </div>

            </flux:card>

            <div class="flex">
                <flux:spacer />

                <flux:button variant="primary" wire:click="save">
                    Save
                </flux:button>
            </div>

        </div>

    </flux:modal>

    {{-- DELETE MODAL --}}
    <x-delete-modal name="delete-shift" heading="Delete Shift?" message="This shift will be permanently deleted."
        action="destroy" />

</div>
