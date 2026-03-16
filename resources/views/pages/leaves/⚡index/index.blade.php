<div class="space-y-6">

    <div>
        <flux:heading>Leave Management</flux:heading>
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

            <div class="w-full sm:w-auto">

                @can('create', App\Models\Leave::class)
                    <flux:button icon="plus" class="w-full sm:w-auto" wire:click="create">
                        Create Leave
                    </flux:button>
                @endcan

            </div>

        </div>

        <flux:table :paginate="$this->leaves">

            <flux:table.columns>

                <flux:table.column>User</flux:table.column>
                <flux:table.column>Leave Type</flux:table.column>
                <flux:table.column>Period</flux:table.column>
                <flux:table.column>Days</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column></flux:table.column>

            </flux:table.columns>

            <flux:table.rows>

                @forelse($this->leaves as $leave)
                    <flux:table.row>

                        <flux:table.cell>
                            {{ $leave->user->name }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $leave->leaveType->name }}
                        </flux:table.cell>

                        <flux:table.cell>

                            <div class="text-sm">

                                {{ $leave->start_date }}

                                <span class="text-zinc-400">
                                    →
                                </span>

                                {{ $leave->end_date }}

                            </div>

                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $leave->days }}
                        </flux:table.cell>

                        <flux:table.cell>

                            <flux:badge color="{{ $leave->status === 'approved' ? 'green' : 'yellow' }}">

                                {{ ucfirst($leave->status) }}

                            </flux:badge>

                        </flux:table.cell>

                        <flux:table.cell class="text-right">

                            <flux:dropdown>

                                <flux:button size="sm" icon="ellipsis-horizontal" />

                                <flux:menu>

                                    @can('update', $leave)
                                        <flux:menu.item icon="pencil" wire:click="edit({{ $leave->id }})">
                                            Edit
                                        </flux:menu.item>
                                    @endcan

                                    @can('approve', App\Models\Leave::class)
                                        <flux:menu.item icon="check" wire:click="approve({{ $leave->id }})">
                                            Approve
                                        </flux:menu.item>

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

                        <flux:table.cell colspan="6">

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

            <div>

                <flux:heading size="lg">

                    {{ $leaveId ? 'Edit Leave' : 'Create Leave' }}

                </flux:heading>

                <flux:text class="mt-2">
                    Manage employee leave request details.
                </flux:text>

            </div>

            <flux:select label="User" wire:model="user_id">

                <option value="">
                    Select User
                </option>

                @foreach ($this->users as $id => $name)
                    <option value="{{ $id }}">
                        {{ $name }}
                    </option>
                @endforeach

            </flux:select>

            <flux:select label="Leave Type" wire:model="leave_type_id">

                <option value="">
                    Select Type
                </option>

                @foreach ($this->leaveTypes as $id => $name)
                    <option value="{{ $id }}">
                        {{ $name }}
                    </option>
                @endforeach

            </flux:select>

            <flux:input label="Start Date" type="date" wire:model="start_date" />

            <flux:input label="End Date" type="date" wire:model="end_date" />

            <flux:input label="Days" type="number" wire:model="days" />

            <flux:textarea label="Reason" wire:model="reason" />

            <div class="flex">
                <flux:spacer />

                <flux:button variant="primary" wire:click="save">
                    Save
                </flux:button>

            </div>

        </div>

    </flux:modal>

    <x-delete-modal name="delete-leave" heading="Delete Leave?"
        message="You're about to delete this leave request.<br>This action cannot be reversed." action="destroy" />

</div>
