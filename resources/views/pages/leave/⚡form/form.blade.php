<div class="space-y-6">

    <x-page-header title="Request Leave" description="Submit leave request and track approval." />

    <form wire:submit.prevent="save">
        <flux:card class="space-y-6">

            <flux:field>
                <flux:label>Type</flux:label>
                <flux:select wire:model="type">
                    <option value="annual">Annual</option>
                    <option value="sick">Sick</option>
                    <option value="permit">Permit</option>
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>Start Date</flux:label>
                <flux:input wire:model="start_date" type="date" />
            </flux:field>

            <flux:field>
                <flux:label>End Date</flux:label>
                <flux:input wire:model="end_date" type="date" />
            </flux:field>

            <flux:field>
                <flux:label>Reason</flux:label>
                <flux:textarea wire:model="reason" rows="3" />
            </flux:field>

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary">
                    Submit
                </flux:button>
            </div>

        </flux:card>
    </form>

</div>
