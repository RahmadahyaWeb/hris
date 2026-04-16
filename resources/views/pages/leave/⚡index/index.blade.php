<div>
    <x-page-header title="Leaves" description="Manage leave requests, approval flow, and progress."
        button-label="Request Leave" :button-href="route('leaves.create')" />

    <flux:card>
        <flux:table :paginate="$this->leaves">

            <flux:table.columns>
                <flux:table.column>Employee</flux:table.column>
                <flux:table.column>Type</flux:table.column>
                <flux:table.column>Period</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Approval Flow</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->leaves as $leave)
                    <flux:table.row :key="$leave->id">

                        <flux:table.cell>
                            {{ $leave->user->name }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ ucfirst($leave->type) }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $leave->start_date }} → {{ $leave->end_date }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <span
                                class="text-xs px-2 py-1 rounded
                                {{ $leave->status === 'approved'
                                    ? 'bg-green-100 text-green-700'
                                    : ($leave->status === 'rejected'
                                        ? 'bg-red-100 text-red-700'
                                        : 'bg-yellow-100 text-yellow-700') }}">
                                {{ ucfirst($leave->status) }}
                            </span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="space-y-1 text-xs">
                                @foreach ($leave->approvals as $approval)
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium">
                                            L{{ $approval->level }}
                                        </span>

                                        <span>
                                            {{ $approval->approver?->name }}
                                        </span>

                                        <span
                                            class="
                                            {{ $approval->status === 'approved'
                                                ? 'text-green-600'
                                                : ($approval->status === 'rejected'
                                                    ? 'text-red-600'
                                                    : 'text-gray-400') }}">
                                            ({{ $approval->status }})
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </flux:table.cell>

                        <flux:table.cell align="end">
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon:trailing="ellipsis-horizontal" />

                                <flux:menu>

                                    <flux:menu.item icon="eye" href="{{ route('leaves.edit', $leave) }}">
                                        View
                                    </flux:menu.item>

                                    @foreach ($leave->approvals as $approval)
                                        @php
                                            $canApprove =
                                                $approval->approver_id === auth()->id() &&
                                                $approval->status === 'pending' &&
                                                !$leave->approvals
                                                    ->where('level', '<', $approval->level)
                                                    ->where('status', '!=', 'approved')
                                                    ->count();
                                        @endphp

                                        @if ($canApprove)
                                            <flux:menu.separator />

                                            <flux:menu.item icon="check" wire:click="approve({{ $approval->id }})">
                                                Approve
                                            </flux:menu.item>

                                            <flux:menu.item icon="x-mark" wire:click="reject({{ $approval->id }})">
                                                Reject
                                            </flux:menu.item>
                                        @endif
                                    @endforeach

                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>

                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center">
                            No leave data found
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>

        </flux:table>
    </flux:card>
</div>
