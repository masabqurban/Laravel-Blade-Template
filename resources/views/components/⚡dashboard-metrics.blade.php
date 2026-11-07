<?php

use App\Models\User;
use App\Support\DashboardMetrics;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    /**
     * @return list<array{key: string, label: string, value: int, change: float}>
     */
    #[Computed]
    public function stats(): array
    {
        return app(DashboardMetrics::class)->stats();
    }

    /**
     * @return list<array{month: string, count: int}>
     */
    #[Computed]
    public function signups(): array
    {
        return app(DashboardMetrics::class)->signups();
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function recentUsers(): Collection
    {
        return User::query()->latest()->latest('id')->limit(5)->get();
    }

    /**
     * Shown while the metrics load.
     */
    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="flex flex-col gap-6" aria-busy="true">
            <span class="sr-only">Loading metrics…</span>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <flux:skeleton class="h-28 rounded-xl" />
                <flux:skeleton class="h-28 rounded-xl" />
                <flux:skeleton class="h-28 rounded-xl" />
                <flux:skeleton class="h-28 rounded-xl" />
            </div>
            <div class="grid gap-4 lg:grid-cols-3">
                <flux:skeleton class="h-80 rounded-xl lg:col-span-2" />
                <flux:skeleton class="h-80 rounded-xl" />
            </div>
        </div>
        HTML;
    }
}; ?>

@php
    $peak = max(1, ...array_column($this->signups, 'count'));
@endphp

<div class="flex flex-col gap-6">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($this->stats as $stat)
            @php
                // Fewer pending invites is the good direction.
                $good = $stat['key'] === 'pending' ? $stat['change'] <= 0 : $stat['change'] >= 0;
            @endphp
            <flux:card class="flex flex-col gap-2" data-stat="{{ $stat['key'] }}">
                <flux:text>{{ __($stat['label']) }}</flux:text>
                <div class="text-3xl font-semibold tabular-nums text-zinc-900 dark:text-white">{{ number_format($stat['value']) }}</div>
                <flux:text size="sm" class="flex items-center gap-1">
                    <span @class([
                        'font-medium tabular-nums',
                        'text-green-700 dark:text-green-400' => $good,
                        'text-red-700 dark:text-red-400' => ! $good,
                    ])>{{ $stat['change'] >= 0 ? '+' : '' }}{{ Number::percentage($stat['change'] * 100, 1) }}</span>
                    {{ __('vs last 30 days') }}
                </flux:text>
            </flux:card>
        @endforeach
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <flux:card class="lg:col-span-2">
            <flux:heading>{{ __('New sign-ups') }}</flux:heading>
            <flux:text size="sm">{{ __('Last six months') }}</flux:text>

            {{-- A CSS bar chart: no chart library to load. The table below gives screen readers the same data. --}}
            <div class="mt-6 flex h-56 items-end gap-3" aria-hidden="true">
                @foreach ($this->signups as $month)
                    <div class="flex h-full flex-1 flex-col items-center justify-end gap-2">
                        <span class="text-xs tabular-nums text-zinc-500 dark:text-zinc-400">{{ $month['count'] }}</span>
                        <div class="w-full rounded-t-md bg-zinc-800 dark:bg-zinc-200" style="height: {{ round($month['count'] / $peak * 100, 1) }}%"></div>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $month['month'] }}</span>
                    </div>
                @endforeach
            </div>
            <table class="sr-only">
                <caption>{{ __('New sign-ups per month') }}</caption>
                <tr><th scope="col">{{ __('Month') }}</th><th scope="col">{{ __('Sign-ups') }}</th></tr>
                @foreach ($this->signups as $month)
                    <tr><td>{{ $month['month'] }}</td><td>{{ $month['count'] }}</td></tr>
                @endforeach
            </table>
        </flux:card>

        <flux:card>
            <div class="flex items-center justify-between">
                <flux:heading>{{ __('Recent users') }}</flux:heading>
                <flux:link :href="route('users.index')" wire:navigate variant="subtle" class="text-sm">{{ __('View all') }}</flux:link>
            </div>
            <ul class="mt-4 flex flex-col gap-4">
                @foreach ($this->recentUsers as $user)
                    <li class="flex items-center gap-3" wire:key="recent-{{ $user->id }}">
                        <flux:avatar size="sm" :name="$user->name" :initials="$user->initials()" />
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-medium text-zinc-800 dark:text-white">{{ $user->name }}</div>
                            <div class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</div>
                        </div>
                        <x-users.status-badge :status="$user->status" />
                    </li>
                @endforeach
            </ul>
        </flux:card>
    </div>
</div>
