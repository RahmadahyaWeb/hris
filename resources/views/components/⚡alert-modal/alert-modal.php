<?php

use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public string $title = 'Alert';

    public string $message = '';

    public string $variant = 'info';

    #[On('alert')]
    public function show(array $payload): void
    {
        $this->title = $payload['title'] ?? 'Alert';
        $this->message = $payload['message'] ?? '';
        $this->variant = $payload['variant'] ?? 'info';

        $this->modal('alert-modal')->show();
    }

    public function close(): void
    {
        $this->modal('alert-modal')->close();
    }
};
