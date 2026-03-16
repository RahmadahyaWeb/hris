<div class="space-y-6">

    <div>
        <flux:heading>Attendance Monitoring</flux:heading>
        <flux:text class="mt-2">
            Monitor employee attendance activity.
        </flux:text>
    </div>

    <flux:card>

        <div class="flex gap-4 mb-6">

            <flux:input type="date" wire:model.live="date" />

        </div>

        <flux:table :paginate="$this->attendances">

            <flux:table.columns>
                <flux:table.column>User</flux:table.column>
                <flux:table.column>Checkin</flux:table.column>
                <flux:table.column>Checkout</flux:table.column>
                <flux:table.column>Status</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>

                @forelse ($this->attendances as $attendance)
                    <flux:table.row>

                        <flux:table.cell>
                            {{ $attendance->user->name }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $attendance->checkin_at }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $attendance->checkout_at ?? '-' }}
                        </flux:table.cell>

                        <flux:table.cell>

                            @if ($attendance->late_minutes > 0)
                                <flux:badge color="yellow">
                                    Late
                                </flux:badge>
                            @else
                                <flux:badge color="green">
                                    On Time
                                </flux:badge>
                            @endif

                        </flux:table.cell>

                    </flux:table.row>

                @empty

                    <flux:table.row>

                        <flux:table.cell colspan="4">

                            <div class="text-center py-10">

                                <flux:heading size="sm">
                                    No Attendance Found
                                </flux:heading>

                                <flux:text class="text-sm text-zinc-500 mt-1">
                                    No employee attendance recorded.
                                </flux:text>

                            </div>

                        </flux:table.cell>

                    </flux:table.row>
                @endforelse

            </flux:table.rows>

        </flux:table>

    </flux:card>

</div>
