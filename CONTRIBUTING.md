# Contributing

Thanks for helping improve this template.

## Development workflow

1. Fork the repository and create a branch from `main`, e.g. `feat/projects-module`.
2. Run `composer setup` once, then `composer dev`.
3. Make your change, then run:

   ```bash
   composer lint    # format PHP with Pint
   composer test    # Pint, PHPStan and Pest
   ```

4. Open a pull request using the template.

## Conventions

- **Commits** follow [Conventional Commits](https://www.conventionalcommits.org/): `feat:`, `fix:`, `docs:`, `refactor:`, `test:`, `chore:`.
- **Pages are Livewire single-file components** in `resources/views/pages/`, registered with `Route::livewire()`.
- **Authorise every action.** Any public Livewire method can be called from the browser with any arguments, so call `$this->authorize()` in each one, not only in `mount()`.
- **Lock what the browser mustn't change.** Mark IDs and models that identify a record with `#[Locked]`.
- **Validation lives in form objects** (`app/Livewire/Forms`), keeping page components short.
- **Every `<script>` needs the CSP nonce.** Use `@vite`, Livewire's `@assets`, or pass `['nonce' => Vite::cspNonce()]` to Flux directives. The security headers test fails otherwise.
- **Keep the schema portable.** Avoid database-specific column types and raw SQL, so SQLite, PostgreSQL and MySQL all keep passing.
- **New PHP files declare `strict_types`.** Architecture tests enforce this for enums, policies, form objects and support classes.
- **Placeholder data only.** Seeders and factories use generated names and `@example.com` addresses.
