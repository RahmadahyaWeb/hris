<div class="space-y-6">

    <div>
        <flux:heading size="lg">Employee Schedule</flux:heading>
        <flux:text class="mt-2">
            Manage employee schedules and generate monthly schedules per user.
        </flux:text>
    </div>

    <flux:card>

        <div class="grid gap-4 mb-6 sm:grid-cols-4">

            <flux:input type="number" wire:model="year" label="Year" />

            <flux:input type="number" wire:model="month" label="Month" />

            <flux:select wire:model="user_id" label="User">

                <option value="">Select User</option>

                @foreach ($this->users as $id => $user)
                    <option value="{{ $id }}">
                        {{ $user }}
                    </option>
                @endforeach

            </flux:select>

            <flux:select wire:model="shift_id" label="Shift">

                <option value="">Select Shift</option>

                @foreach ($this->shifts as $id => $shift)
                    <option value="{{ $id }}">
                        {{ $shift }}
                    </option>
                @endforeach

            </flux:select>

        </div>

        <div class="flex justify-end mb-6">

            <flux:button icon="calendar" wire:click="generateUserSchedule">
                Generate Schedule
            </flux:button>

        </div>

        <flux:table :paginate="$this->schedules">

            <flux:table.columns>
                <flux:table.column>User</flux:table.column>
                <flux:table.column>Shift</flux:table.column>
                <flux:table.column>Date</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>

                @forelse ($this->schedules as $schedule)
                    <flux:table.row>

                        <flux:table.cell>
                            {{ $schedule->user->name }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $schedule->shift->name }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $schedule->date->format('Y-m-d') }}
                        </flux:table.cell>

                        <flux:table.cell class="text-right">

                            <flux:dropdown>

                                <flux:button size="sm" icon="ellipsis-horizontal" />

                                <flux:menu>

                                    <flux:menu.item icon="pencil" wire:click="edit({{ $schedule->id }})">
                                        Edit
                                    </flux:menu.item>

                                    <flux:menu.separator />

                                    <flux:menu.item icon="trash" variant="danger"
                                        wire:click="confirmDelete({{ $schedule->id }})">
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
                                    No Schedule Found
                                </flux:heading>

                                <flux:text class="mt-1 text-sm text-zinc-500">
                                    Generate a schedule using the form above.
                                </flux:text>

                            </div>

                        </flux:table.cell>

                    </flux:table.row>
                @endforelse

            </flux:table.rows>

        </flux:table>

    </flux:card>

    <flux:modal name="schedule-form" class="md:w-[28rem]">

        <div class="space-y-6">

            <flux:heading size="lg">
                {{ $scheduleId ? 'Edit Schedule' : 'Create Schedule' }}
            </flux:heading>

            <flux:select label="Employee" wire:model="user_id">

                <option value="">Select Employee</option>

                @foreach ($this->users as $id => $user)
                    <option value="{{ $id }}">
                        {{ $user }}
                    </option>
                @endforeach

            </flux:select>

            <flux:select label="Shift" wire:model="shift_id">

                <option value="">Select Shift</option>

                @foreach ($this->shifts as $id => $shift)
                    <option value="{{ $id }}">
                        {{ $shift }}
                    </option>
                @endforeach

            </flux:select>

            <flux:input type="date" label="Date" wire:model="date" />

            <div class="flex">

                <flux:spacer />

                <flux:button variant="primary" wire:click="save">
                    Save
                </flux:button>

            </div>

        </div>

    </flux:modal>

    <x-delete-modal name="delete-schedule" heading="Delete Schedule?"
        message="You're about to delete this schedule.<br>This action cannot be reversed." action="destroy" />

</div>
