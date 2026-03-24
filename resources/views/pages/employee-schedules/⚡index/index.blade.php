<div class="space-y-6">

    {{-- HEADER --}}
    <div>
        <flux:heading size="lg">Employee Schedule</flux:heading>
        <flux:text class="mt-1 text-sm text-zinc-500">
            Generate and manage employee schedules efficiently.
        </flux:text>
    </div>

    {{-- GENERATOR --}}
    <flux:card>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">

            <flux:input type="number" wire:model="year" label="Year" />

            <flux:select wire:model="month" label="Start Month">
                @foreach (range(1, 12) as $m)
                    <option value="{{ $m }}">
                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                    </option>
                @endforeach
            </flux:select>

            <flux:select wire:model="duration_months" label="Duration">
                <option value="1">1 Month</option>
                <option value="6">6 Months</option>
                <option value="12">12 Months</option>
            </flux:select>

            <flux:select wire:model="user_id" label="Employee">
                <option value="">Select</option>
                @foreach ($this->users as $id => $user)
                    <option value="{{ $id }}">{{ $user }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model="shift_id" label="Shift">
                <option value="">Select</option>
                @foreach ($this->shifts as $id => $shift)
                    <option value="{{ $id }}">{{ $shift }}</option>
                @endforeach
            </flux:select>

        </div>

        <div class="flex flex-col gap-2 mt-4 sm:flex-row sm:justify-between sm:items-center">

            <div class="text-xs text-zinc-500">
                Generates schedule from selected month for selected duration
            </div>

            <flux:button icon="calendar" wire:click="generateUserSchedule">
                Generate Schedule
            </flux:button>

        </div>

    </flux:card>

    {{-- FILTER --}}
    <flux:card>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">

            <flux:select wire:model.live="filter_user_id" label="Employee">
                <option value="">All Employees</option>
                @foreach ($this->users as $id => $user)
                    <option value="{{ $id }}">{{ $user }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="division_id" label="Division">
                <option value="">All Divisions</option>
                @foreach ($this->divisions as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filter_shift_id" label="Shift">
                <option value="">All Shifts</option>
                @foreach ($this->shifts as $id => $shift)
                    <option value="{{ $id }}">{{ $shift }}</option>
                @endforeach
            </flux:select>

            <div class="flex items-end">
                <flux:button variant="ghost" wire:click="$refresh">
                    Reset
                </flux:button>
            </div>

        </div>

    </flux:card>

    {{-- TABLE --}}
    <flux:card>

        <flux:table :paginate="$this->schedules">

            <flux:table.columns>
                <flux:table.column>Employee</flux:table.column>
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
                            {{ $schedule->date->format('d M Y') }}
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

                            <div class="flex flex-col items-center py-10">

                                <flux:heading size="sm">
                                    No Schedule Found
                                </flux:heading>

                                <flux:text class="text-sm text-zinc-500 mt-1">
                                    Generate schedule using the form above
                                </flux:text>

                            </div>

                        </flux:table.cell>
                    </flux:table.row>
                @endforelse

            </flux:table.rows>

        </flux:table>

    </flux:card>

</div>
