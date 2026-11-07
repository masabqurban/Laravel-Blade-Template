<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')] class extends Component {
    //
}; ?>

<section class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl" level="1">{{ __('Dashboard') }}</flux:heading>
        <flux:subheading>{{ __('Welcome back, :name. Here\'s what\'s happening.', ['name' => Str::before(auth()->user()->name, ' ')]) }}</flux:subheading>
    </div>

    {{-- The page shell renders at once; the metrics load in a follow-up request. --}}
    <livewire:dashboard-metrics lazy />
</section>
