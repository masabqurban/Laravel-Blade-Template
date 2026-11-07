@php
    $features = [
        ['icon' => 'users', 'title' => 'User management', 'text' => 'Search, filters and pagination kept in the URL. Add, edit and delete in modals.'],
        ['icon' => 'shield-check', 'title' => 'Roles & policies', 'text' => 'Admin, Editor and Viewer roles enforced by policies on every Livewire action.'],
        ['icon' => 'key', 'title' => 'Modern auth', 'text' => 'Passkeys, two-factor authentication, email verification and password reset.'],
        ['icon' => 'chart-bar', 'title' => 'Lazy dashboard', 'text' => 'The page renders instantly while the metrics load right after.'],
        ['icon' => 'bolt', 'title' => 'Livewire 4', 'text' => 'Single-file components: PHP and Blade together, with no API to build.'],
        ['icon' => 'circle-stack', 'title' => 'Any database', 'text' => 'SQLite out of the box. PostgreSQL and MySQL are tested in CI on every push.'],
    ];
    $stack = ['Laravel 13', 'Livewire 4', 'Flux UI', 'Alpine.js', 'Tailwind CSS 4', 'Pest', 'PHPStan'];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head', ['title' => __('Welcome')])
    </head>
    <body class="flex min-h-svh flex-col bg-white text-zinc-900 antialiased dark:bg-zinc-900 dark:text-zinc-100">
        <header class="mx-auto flex w-full max-w-6xl items-center justify-between p-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold">
                <span class="flex size-8 items-center justify-center rounded-md bg-zinc-900 text-white dark:bg-white dark:text-zinc-900">
                    <x-app-logo-icon class="size-5" />
                </span>
                {{ config('app.name') }}
            </a>
            <nav class="flex items-center gap-2">
                @auth
                    <flux:button variant="primary" :href="route('dashboard')">{{ __('Dashboard') }}</flux:button>
                @else
                    <flux:button variant="ghost" :href="route('login')">{{ __('Log in') }}</flux:button>
                    @if (Route::has('register'))
                        <flux:button :href="route('register')">{{ __('Register') }}</flux:button>
                    @endif
                @endauth
            </nav>
        </header>

        <main class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-16 px-6 pt-10 pb-20 lg:pt-20">
            <section class="flex max-w-3xl flex-col gap-6">
                <p class="w-fit rounded-full border border-zinc-200 px-3 py-1 text-xs font-medium text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                    {{ __('Laravel + Livewire + Blade template') }}
                </p>
                <h1 class="text-4xl font-semibold tracking-tight text-balance sm:text-5xl">
                    {{ __('A production-ready starting point for your next Laravel app.') }}
                </h1>
                <p class="max-w-2xl text-lg text-pretty text-zinc-600 dark:text-zinc-400">
                    {{ __('Server-rendered Blade with Livewire for interactivity: no API layer and no separate frontend app to maintain. Authentication, roles, security headers, Docker and CI are already in place.') }}
                </p>
                <div class="flex flex-wrap items-center gap-3">
                    <flux:button variant="primary" icon-trailing="arrow-right" :href="auth()->check() ? route('dashboard') : route('login')">
                        {{ auth()->check() ? __('Open the dashboard') : __('Try the demo') }}
                    </flux:button>
                    <flux:button href="https://github.com/masabqurban/Laravel-Blade-Template" target="_blank" rel="noreferrer">
                        {{ __('View on GitHub') }}
                    </flux:button>
                </div>
                @guest
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Demo account:') }}
                        <code class="rounded bg-zinc-100 px-1.5 py-0.5 font-mono text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100">demo@example.com</code>
                        /
                        <code class="rounded bg-zinc-100 px-1.5 py-0.5 font-mono text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100">password</code>
                    </p>
                @endguest
            </section>

            <section aria-labelledby="features-heading">
                <h2 id="features-heading" class="sr-only">{{ __('Features') }}</h2>
                <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($features as $feature)
                        <li class="flex flex-col gap-3 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
                            <flux:icon :name="$feature['icon']" variant="outline" class="size-5 text-zinc-500 dark:text-zinc-400" />
                            <h3 class="font-medium">{{ __($feature['title']) }}</h3>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __($feature['text']) }}</p>
                        </li>
                    @endforeach
                </ul>
            </section>
        </main>

        <footer class="border-t border-zinc-200 dark:border-zinc-700">
            <ul class="mx-auto flex max-w-6xl flex-wrap gap-x-6 gap-y-2 p-6 text-sm text-zinc-500 dark:text-zinc-400" aria-label="{{ __('Built with') }}">
                @foreach ($stack as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </footer>

        @fluxScripts(['nonce' => Vite::cspNonce()])
    </body>
</html>
