<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <flux:heading>My Leave</flux:heading>
            <flux:text class="text-zinc-500">
                Manage your leave requests and track approval progress.
            </flux:text>
        </div>

        <flux:button icon="plus" wire:click="create" class="w-full sm:w-auto">
            Apply Leave
        </flux:button>

    </div>

    {{-- BALANCE --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

        @foreach ($this->balances as $balance)
            <flux:card class="p-3">

                <div class="flex flex-col">

                    <span class="text-xs text-zinc-500">
                        {{ $balance->leaveType->name }}
                    </span>

                    <span class="text-lg font-semibold">
                        {{ $balance->remaining_days }} days
                    </span>

                    <span class="text-xs text-zinc-400">
                        Used: {{ $balance->used_days }}
                    </span>

                </div>

            </flux:card>
        @endforeach

    </div>

    {{-- LIST --}}
    <flux:card class="overflow-x-auto">

        <flux:table :paginate="$this->leaves">

            <flux:table.columns>

                <flux:table.column>Type</flux:table.column>
                <flux:table.column>Period</flux:table.column>
                <flux:table.column>Days</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Progress</flux:table.column>
                <flux:table.column></flux:table.column>

            </flux:table.columns>

            <flux:table.rows>

                @forelse ($this->leaves as $leave)
                    @php
                        $totalSteps = $leave->leaveType->approvalSteps->count() ?? 0;
                        $current = $leave->current_level ?? 0;

                        $percent = $totalSteps > 0 ? intval(($current / $totalSteps) * 100) : 0;
                    @endphp

                    <flux:table.row>

                        {{-- TYPE --}}
                        <flux:table.cell>
                            {{ $leave->leaveType->name }}
                        </flux:table.cell>

                        {{-- PERIOD --}}
                        <flux:table.cell>
                            <div class="text-sm">
                                {{ $leave->start_date }}
                                <span class="text-zinc-400">→</span>
                                {{ $leave->end_date }}
                            </div>
                        </flux:table.cell>

                        {{-- DAYS --}}
                        <flux:table.cell>
                            {{ $leave->days }}
                        </flux:table.cell>

                        {{-- STATUS --}}
                        <flux:table.cell>

                            <flux:badge
                                color="{{ match ($leave->status) {
                                    'approved' => 'green',
                                    'rejected' => 'red',
                                    default => 'yellow',
                                } }}">

                                {{ ucfirst($leave->status) }}

                            </flux:badge>

                        </flux:table.cell>

                        {{-- PROGRESS --}}
                        <flux:table.cell>

                            <div class="flex flex-col gap-1 w-32">

                                <div class="text-xs text-zinc-500">
                                    {{ $current }} / {{ $totalSteps }}
                                </div>

                                <div class="w-full h-2 bg-zinc-200 rounded">

                                    <div class="h-2 rounded bg-blue-500" style="width: {{ $percent }}%">
                                    </div>

                                </div>

                                @if ($leave->status === 'pending')
                                    <div class="text-[10px] text-zinc-400">
                                        Waiting approval step {{ $current + 1 }}
                                    </div>
                                @elseif ($leave->status === 'approved')
                                    <div class="text-[10px] text-green-500">
                                        Completed
                                    </div>
                                @elseif ($leave->status === 'rejected')
                                    <div class="text-[10px] text-red-500">
                                        Rejected
                                    </div>
                                @endif

                            </div>

                        </flux:table.cell>

                        <flux:table.cell>

                            <flux:dropdown>

                                <flux:button size="sm" icon="ellipsis-horizontal" />

                                <flux:menu>

                                    <flux:menu.item icon="clock" wire:click="timeline({{ $leave->id }})">
                                        Timeline
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
                                    No Leave Data
                                </flux:heading>

                                <flux:text class="text-sm text-zinc-500">
                                    You have not submitted any leave.
                                </flux:text>

                            </div>

                        </flux:table.cell>

                    </flux:table.row>
                @endforelse

            </flux:table.rows>

        </flux:table>

    </flux:card>

    {{-- MODAL --}}
    <flux:modal name="leave-form" class="md:w-96">

        <div class="space-y-4">

            <flux:heading size="lg">
                Apply Leave
            </flux:heading>

            <flux:select label="Leave Type" wire:model="leave_type_id">

                <option value="">Select Type</option>

                @foreach ($this->leaveTypes as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach

            </flux:select>

            <flux:input type="date" label="Start Date" wire:model="start_date" />

            <flux:input type="date" label="End Date" wire:model="end_date" />

            <flux:input type="number" label="Days" wire:model="days" />

            <flux:textarea label="Reason" wire:model="reason" />

            <div class="flex">
                <flux:spacer />
                <flux:button wire:click="save">
                    Submit
                </flux:button>
            </div>

        </div>

    </flux:modal>

    <flux:modal name="leave-timeline" class="md:w-96">

        <flux:heading size="lg">
            Approval Timeline
        </flux:heading>

        <div class="mt-4 space-y-4">

            @php
                $steps = $selectedLeave?->leaveType?->approvalSteps ?? collect();
                $histories = $selectedLeave?->histories ?? collect();
            @endphp

            @foreach ($steps as $index => $step)
                @php
                    $history = $histories->firstWhere('step', $index + 1);
                @endphp

                <div class="flex items-start gap-3">

                    <div class="mt-1">
                        <div class="w-3 h-3 rounded-full {{ $history ? 'bg-green-500' : 'bg-zinc-300' }}"></div>
                    </div>

                    <div class="flex-1">

                        <div class="flex items-center justify-between">

                            <div class="text-sm font-medium">
                                Step {{ $index + 1 }}
                            </div>

                            @if ($history)
                                <flux:badge color="{{ $history->action === 'approved' ? 'green' : 'red' }}">
                                    {{ ucfirst($history->action) }}
                                </flux:badge>
                            @else
                                <flux:badge color="zinc">
                                    Waiting
                                </flux:badge>
                            @endif

                        </div>

                        <div class="text-xs text-zinc-500 mt-1">

                            @if ($history)
                                {{ $history->approver->name ?? '-' }}<br>
                                {{ $history->acted_at }}
                            @else
                                Pending approval
                            @endif

                        </div>

                    </div>

                </div>
            @endforeach

        </div>

    </flux:modal>

</div>
