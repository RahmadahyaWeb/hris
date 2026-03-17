<div class="space-y-6">

    <div>
        <flux:heading size="lg">Device Security Dashboard</flux:heading>
        <flux:text class="mt-2">
            Manage and monitor user devices. Approve, block, or revoke devices when necessary.
        </flux:text>
    </div>

    <flux:card>

        <flux:table :paginate="$this->users">

            <flux:table.columns>
                <flux:table.column>User</flux:table.column>
                <flux:table.column>Devices</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>

                @foreach ($this->users as $user)
                    <flux:table.row>

                        <flux:table.cell>

                            <div class="flex flex-col">
                                <span class="font-medium">{{ $user->name }}</span>
                                <span class="text-xs text-zinc-500">
                                    {{ $user->email }}
                                </span>
                            </div>

                        </flux:table.cell>

                        <flux:table.cell>

                            <flux:badge color="{{ $user->devices_count >= $maxDevices ? 'red' : 'zinc' }}">
                                {{ $user->devices_count }} / {{ $maxDevices }}
                            </flux:badge>

                        </flux:table.cell>

                        <flux:table.cell>

                            @if ($user->devices->where('status', 'pending')->count())
                                <flux:badge color="yellow">
                                    Pending Approval
                                </flux:badge>
                            @else
                                <flux:badge color="green">
                                    Secure
                                </flux:badge>
                            @endif

                        </flux:table.cell>

                        <flux:table.cell>

                            <flux:dropdown>

                                <flux:button size="sm" icon="chevron-down" />

                                <flux:menu>

                                    @foreach ($user->devices as $device)
                                        <flux:menu.separator />

                                        <flux:menu.item>
                                            {{ $device->device_name ?? 'Unknown Device' }}
                                        </flux:menu.item>

                                        @if ($device->status === 'pending')
                                            <flux:menu.item icon="check" wire:click="approve({{ $device->id }})">
                                                Approve
                                            </flux:menu.item>
                                        @endif

                                        @if ($device->status === 'approved')
                                            <flux:menu.item icon="no-symbol" variant="danger"
                                                wire:click="block({{ $device->id }})">
                                                Block
                                            </flux:menu.item>
                                        @endif

                                        <flux:menu.item icon="trash" variant="danger"
                                            wire:click="revoke({{ $device->id }})">
                                            Revoke
                                        </flux:menu.item>
                                    @endforeach

                                </flux:menu>

                            </flux:dropdown>

                        </flux:table.cell>

                    </flux:table.row>
                @endforeach

            </flux:table.rows>

        </flux:table>

    </flux:card>

</div>
