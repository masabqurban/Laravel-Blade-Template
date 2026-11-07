# Agent instructions

- Read `README.md` (architecture, roles, security) and `CONTRIBUTING.md` (conventions) first.
- Laravel 13 + Livewire 4 + Blade + Flux UI. Pages are single-file components (`⚡name.blade.php`) in `resources/views/pages`, registered with `Route::livewire('users', 'pages::users.index')`.
- Every public Livewire action must call `$this->authorize()`; mark record identifiers `#[Locked]`. Validation goes in `app/Livewire/Forms`.
- Every `<script>` must carry the CSP nonce (`Vite::cspNonce()`); `tests/Feature/SecurityHeadersTest.php` checks this.
- Keep migrations portable across SQLite, PostgreSQL and MySQL.
- Feature tests render pages through Vite, so run `npm run build` before `php artisan test` if the manifest is missing.
- Run `composer test` before considering work done.
