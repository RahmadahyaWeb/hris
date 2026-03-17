<div class="space-y-6">

    <div>
        <flux:heading size="lg">Leave Management</flux:heading>
        <flux:text class="mt-2">
            Manage employee leave requests and approvals.
        </flux:text>
    </div>

    <flux:card>

        <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <flux:heading size="lg">
                    Leave Requests
                </flux:heading>

                <flux:text class="mt-1 text-sm">
                    View and manage employee leave applications.
                </flux:text>
            </div>

            @can('create', App\Models\Leave::class)
                <flux:button icon="plus" wire:click="create">
                    Create Leave
                </flux:button>
            @endcan

        </div>

        <flux:table :paginate="$this->leaves">

            <flux:table.columns>
                <flux:table.column>User</flux:table.column>
                <flux:table.column>Leave Type</flux:table.column>
                <flux:table.column>Period</flux:table.column>
                <flux:table.column>Days</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Approval</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>

                @forelse($this->leaves as $leave)

                    @php
                        $totalSteps = $leave->leaveType->approvalSteps->count();
                    @endphp

                    <flux:table.row>

                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span class="font-medium">
                                    {{ $leave->user->name }}
                                </span>
                                <span class="text-xs text-zinc-400">
                                    {{ $leave->user->position->title ?? '' }}
                                </span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $leave->leaveType->name }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $leave->start_date }}
                            <span class="text-zinc-400">→</span>
                            {{ $leave->end_date }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $leave->days }}
                        </flux:table.cell>

                        <flux:table.cell>

                            <flux:badge
                                color="{{ $leave->status === 'approved' ? 'green' : ($leave->status === 'rejected' ? 'red' : 'yellow') }}">

                                {{ ucfirst($leave->status) }}

                            </flux:badge>

                        </flux:table.cell>

                        <flux:table.cell>

                            @if ($leave->status === 'pending')
                                <flux:badge color="yellow">
                                    {{ $leave->current_level }}/{{ $totalSteps }}
                                </flux:badge>
                            @elseif($leave->status === 'approved')
                                <flux:badge color="green">
                                    {{ $totalSteps }}/{{ $totalSteps }}
                                </flux:badge>
                            @else
                                <flux:badge color="red">
                                    Rejected
                                </flux:badge>
                            @endif

                        </flux:table.cell>

                        <flux:table.cell class="text-right">

                            <flux:dropdown>

                                <flux:button size="sm" icon="ellipsis-horizontal" />

                                <flux:menu>

                                    <flux:menu.item icon="clock" wire:click="timeline({{ $leave->id }})">
                                        Timeline
                                    </flux:menu.item>

                                    @can('approve', App\Models\Leave::class)
                                        @if ($leave->status === 'pending' && $leave->current_level < $totalSteps)
                                            <flux:menu.item icon="check" wire:click="approve({{ $leave->id }})">
                                                Approve
                                            </flux:menu.item>
                                        @endif

                                        <flux:menu.item icon="x-mark" variant="danger"
                                            wire:click="reject({{ $leave->id }})">
                                            Reject
                                        </flux:menu.item>
                                    @endcan

                                    @can('delete', $leave)
                                        <flux:menu.separator />

                                        <flux:menu.item icon="trash" variant="danger"
                                            wire:click="confirmDelete({{ $leave->id }})">
                                            Delete
                                        </flux:menu.item>
                                    @endcan

                                </flux:menu>

                            </flux:dropdown>

                        </flux:table.cell>

                    </flux:table.row>

                @empty

                    <flux:table.row>

                        <flux:table.cell colspan="7">

                            <div class="flex flex-col items-center justify-center py-10">

                                <flux:heading size="sm">
                                    No Leave Requests
                                </flux:heading>

                                <flux:text class="mt-1 text-sm text-zinc-500">
                                    There are currently no leave applications.
                                </flux:text>

                            </div>

                        </flux:table.cell>

                    </flux:table.row>

                @endforelse

            </flux:table.rows>

        </flux:table>

    </flux:card>


    <flux:modal name="leave-form" class="md:w-96">

        <div class="space-y-6">

            <flux:heading size="lg">
                {{ $leaveId ? 'Edit Leave' : 'Create Leave' }}
            </flux:heading>

            <flux:select label="User" wire:model="user_id">
                <option value="">Select User</option>
                @foreach ($this->users as $id => $name)
                    <option value="{{ $id }}">
                        {{ $name }}
                    </option>
                @endforeach
            </flux:select>

            <flux:select label="Leave Type" wire:model="leave_type_id">
                <option value="">Select Type</option>
                @foreach ($this->leaveTypes as $id => $name)
                    <option value="{{ $id }}">
                        {{ $name }}
                    </option>
                @endforeach
            </flux:select>

            <flux:input type="date" label="Start Date" wire:model="start_date" />

            <flux:input type="date" label="End Date" wire:model="end_date" />

            <flux:input type="number" label="Days" wire:model="days" />

            <flux:textarea label="Reason" wire:model="reason" />

            <div class="flex">
                <flux:spacer />

                <flux:button variant="primary" wire:click="save">
                    Save
                </flux:button>

            </div>

        </div>

    </flux:modal>


    <flux:modal name="leave-timeline">

        <flux:heading size="lg">
            Approval Timeline
        </flux:heading>

        <div class="mt-4 space-y-4">

            @foreach ($selectedLeave?->histories ?? [] as $history)
                <div class="flex items-center justify-between">

                    <div>

                        <div class="font-medium">
                            {{ $history->approver->name }}
                        </div>

                        <div class="text-xs text-zinc-500">
                            Step {{ $history->step }}
                        </div>

                    </div>

                    <flux:badge color="{{ $history->action === 'approved' ? 'green' : 'red' }}">
                        {{ ucfirst($history->action) }}
                    </flux:badge>

                </div>
            @endforeach

        </div>

    </flux:modal>


    <x-delete-modal name="delete-leave" heading="Delete Leave?"
        message="You're about to delete this leave request.<br>This action cannot be reversed." action="destroy" />

</div>
