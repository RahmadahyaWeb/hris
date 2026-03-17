<div class="space-y-6">

    <div>
        <flux:heading size="lg">Attendance Rules</flux:heading>
        <flux:text class="mt-2">
            Configure system rules for lateness, overtime and early checkout.
        </flux:text>
    </div>

    <flux:card>

        <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <flux:heading size="lg">Rule List</flux:heading>
                <flux:text class="mt-1 text-sm">
                    Manage attendance calculation rules.
                </flux:text>
            </div>

            <div class="w-full sm:w-auto">
                <flux:button class="w-full sm:w-auto" icon="plus" wire:click="create">
                    Create Rule
                </flux:button>
            </div>

        </div>

        <flux:table :paginate="$this->attendanceRules">

            <flux:table.columns>
                <flux:table.column>Late Tolerance</flux:table.column>
                <flux:table.column>Early Checkout</flux:table.column>
                <flux:table.column>Overtime</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>

                @forelse ($this->attendanceRules as $rule)
                    <flux:table.row>

                        <flux:table.cell>
                            {{ $rule->late_tolerance_minutes }} minutes
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $rule->early_checkout_tolerance }} minutes
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $rule->overtime_after_minutes }} minutes
                        </flux:table.cell>

                        <flux:table.cell class="text-right">

                            <flux:dropdown>

                                <flux:button size="sm" icon="ellipsis-horizontal" />

                                <flux:menu>

                                    <flux:menu.item icon="pencil" wire:click="edit({{ $rule->id }})">
                                        Edit
                                    </flux:menu.item>

                                    <flux:menu.separator />

                                    <flux:menu.item icon="trash" variant="danger"
                                        wire:click="confirmDelete({{ $rule->id }})">
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
                                    No Rules Found
                                </flux:heading>

                                <flux:text class="mt-1 text-sm text-zinc-500">
                                    Attendance rules have not been configured.
                                </flux:text>

                            </div>

                        </flux:table.cell>

                    </flux:table.row>
                @endforelse

            </flux:table.rows>

        </flux:table>

    </flux:card>

    <flux:modal name="rule-form" class="md:w-96">

        <div class="space-y-6">

            <div>

                <flux:heading size="lg">
                    {{ $ruleId ? 'Edit Rule' : 'Create Rule' }}
                </flux:heading>

                <flux:text class="mt-2">
                    Configure attendance tolerance rules.
                </flux:text>

            </div>

            <flux:input label="Late tolerance (minutes)" type="number" wire:model="late_tolerance_minutes" />

            <flux:input label="Early checkout tolerance (minutes)" type="number"
                wire:model="early_checkout_tolerance" />

            <flux:input label="Overtime threshold (minutes)" type="number" wire:model="overtime_after_minutes" />

            <div class="flex">
                <flux:spacer />

                <flux:button variant="primary" wire:click="save">
                    Save
                </flux:button>

            </div>

        </div>

    </flux:modal>

    <x-delete-modal name="delete-rule" heading="Delete Rule?"
        message="This attendance rule will be permanently removed." action="destroy" />

</div>
