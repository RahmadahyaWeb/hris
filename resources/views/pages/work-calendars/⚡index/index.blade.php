<div class="space-y-6">

    <div>
        <flux:heading size="lg">Work Calendar</flux:heading>
        <flux:text class="mt-2">
            Manage working days, holidays, and generate yearly calendars.
        </flux:text>
    </div>

    <flux:card>

        <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">

            <div class="flex flex-wrap gap-2">

                <flux:input type="number" wire:model="year" class="w-32" />

                <flux:button icon="calendar" wire:click="generateYear">
                    Generate Year
                </flux:button>

                <flux:button variant="ghost" icon="sun" wire:click="markWeekendHoliday">
                    Mark Weekends
                </flux:button>

            </div>

            <flux:button icon="plus" wire:click="create">
                Create Calendar
            </flux:button>

        </div>

        <flux:table :paginate="$this->calendars">

            <flux:table.columns>
                <flux:table.column>Date</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Description</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>

                @forelse ($this->calendars as $calendar)
                    <flux:table.row>

                        <flux:table.cell>
                            {{ $calendar->date->format('Y-m-d') }}
                        </flux:table.cell>

                        <flux:table.cell>

                            @if ($calendar->is_holiday)
                                <flux:badge color="red">
                                    Holiday
                                </flux:badge>
                            @else
                                <flux:badge color="green">
                                    Working Day
                                </flux:badge>
                            @endif

                        </flux:table.cell>

                        <flux:table.cell>

                            <span class="text-sm">
                                {{ $calendar->description ?? '-' }}
                            </span>

                        </flux:table.cell>

                        <flux:table.cell class="text-right">

                            <flux:dropdown>

                                <flux:button size="sm" icon="ellipsis-horizontal" />

                                <flux:menu>

                                    <flux:menu.item icon="pencil" wire:click="edit({{ $calendar->id }})">
                                        Edit
                                    </flux:menu.item>

                                    <flux:menu.separator />

                                    <flux:menu.item icon="trash" variant="danger"
                                        wire:click="confirmDelete({{ $calendar->id }})">
                                        Delete
                                    </flux:menu.item>

                                </flux:menu>

                            </flux:dropdown>

                        </flux:table.cell>

                    </flux:table.row>

                @empty

                    <flux:table.row>

                        <flux:table.cell colspan="4">

                            <div class="flex flex-col items-center justify-center py-12">

                                <flux:heading size="sm">
                                    No Calendar Data
                                </flux:heading>

                                <flux:text class="mt-1 text-sm text-zinc-500">
                                    Generate a yearly calendar or create entries manually.
                                </flux:text>

                                <flux:button class="mt-4" icon="calendar" wire:click="generateYear">

                                    Generate Calendar

                                </flux:button>

                            </div>

                        </flux:table.cell>

                    </flux:table.row>
                @endforelse

            </flux:table.rows>

        </flux:table>

    </flux:card>

    <flux:modal name="calendar-form" class="md:w-[28rem]">

        <div class="space-y-6">

            <flux:heading size="lg">
                {{ $calendarId ? 'Edit Calendar' : 'Create Calendar' }}
            </flux:heading>

            <flux:input type="date" label="Date" wire:model="date" />

            <flux:checkbox label="Holiday" wire:model="is_holiday" />

            <flux:input label="Description" wire:model="description" placeholder="Example: National Holiday" />

            <div class="flex">
                <flux:spacer />

                <flux:button variant="primary" wire:click="save">
                    Save
                </flux:button>

            </div>

        </div>

    </flux:modal>

    <x-delete-modal name="delete-calendar" heading="Delete Calendar?"
        message="You're about to delete this calendar entry.<br>This action cannot be reversed." action="destroy" />

</div>
