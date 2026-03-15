<flux:modal name="alert-modal" class="min-w-[22rem]">

    <div class="space-y-6">

        <div>

            <flux:heading size="lg">
                {{ $title }}
            </flux:heading>

            <flux:text class="mt-2">
                {!! $message !!}
            </flux:text>

        </div>

        <div class="flex gap-2">

            <flux:spacer />

            <flux:button :variant="$variant === 'danger' ? 'danger' : 'primary'" wire:click="close">
                OK
            </flux:button>

        </div>

    </div>

</flux:modal>