# Laravel + Livewire + Blade Template

A production-ready **Laravel 13 + Livewire 4 + Blade** starter. Built on the official Laravel Livewire starter kit, it adds role-based user management, a lazy-loaded dashboard, a nonce-based Content Security Policy, a Docker image, and CI across **SQLite, PostgreSQL and MySQL**. It runs with **zero setup**: SQLite by default, no Docker required.

![PHP](https://img.shields.io/badge/PHP-8.3%2B-777bb4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13-ff2d20?logo=laravel&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire-4-fb70a9?logo=livewire&logoColor=white)
![Flux UI](https://img.shields.io/badge/Flux_UI-2-18181b)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4-06b6d4?logo=tailwindcss&logoColor=white)
![PHPStan](https://img.shields.io/badge/PHPStan-level_8-2a5ea7)
![License: MIT](https://img.shields.io/badge/License-MIT-green)

> All data in this repository (users, names, emails) is generated placeholder data. Every email address uses the reserved `example.com` domain.
> Prefer a React frontend? The [Laravel + Inertia + React Template](https://github.com/masabqurban/Laravel-Inertia-React-Template) has the same features and backend.

## Contents

- [Highlights](#highlights)
- [Use this template](#use-this-template)
- [Getting started](#getting-started)
- [Choosing a database](#choosing-a-database)
- [Scripts](#scripts)
- [Project structure](#project-structure)
- [Architecture](#architecture)
- [Users and roles](#users-and-roles)
- [Security](#security)
- [Testing and quality](#testing-and-quality)
- [Docker](#docker)
- [Deployment](#deployment)
- [Customising the template](#customising-the-template)

## Highlights

| Area               | What's included                                                                                                                         |
| ------------------ | --------------------------------------------------------------------------------------------------------------------------------------- |
| **Stack**          | Laravel 13, Livewire 4 single-file components, Blade, [Flux UI](https://fluxui.dev) 2, Alpine.js, Tailwind CSS 4, Vite                  |
| **Auth**           | Fortify: sign-in, registration, email verification, password reset, **passkeys** and **two-factor authentication**                      |
| **Users module**   | Search, filters and pagination kept in the URL; add, edit and delete in modals; a policy checks every Livewire action                   |
| **Roles & status** | `Admin` / `Editor` / `Viewer` roles and `Active` / `Invited` / `Suspended` statuses as PHP enums; suspended users are signed out at once |
| **Dashboard**      | Stats, a six-month sign-up chart and recent users in a **lazy** Livewire component, so the page renders immediately                      |
| **Security**       | Nonce-based Content Security Policy covering Vite, Livewire and Flux scripts, HSTS, frame and MIME protections, strict Eloquent models  |
| **Database**       | Portable schema: SQLite out of the box, PostgreSQL or MySQL by changing `.env`. CI runs the full suite on all three                     |
| **Quality**        | Pest (62 tests, architecture rules), PHPStan level 8 (Larastan), Pint, GitHub Actions, Dependabot                                       |
| **Operations**     | Multi-stage Docker image on FrankenPHP running as non-root, health check, `compose.yaml` with PostgreSQL, MySQL and Mailpit             |

## Use this template

**1. Create your repository.** Click **Use this template → Create a new repository** at the top of this page. Your new repository gets a clean history and no link back to this one. Or use the GitHub CLI:

```bash
gh repo create my-app --template masabqurban/Laravel-Blade-Template --private --clone
cd my-app
```

Prefer no Git history at all? Run `npx degit masabqurban/Laravel-Blade-Template my-app`.

**2. Install and run it.**

```bash
composer setup    # dependencies, .env, app key, migrations, demo data, asset build
composer dev      # http://localhost:8000, demo@example.com / password
```

Or with Docker: `php artisan key:generate`, then `docker compose up --build` (http://localhost:8080).

**3. Make it yours.**

- `composer.json`: change `"name"` from `masabqurban/laravel-blade-template`, and update the description and keywords.
- `APP_NAME` in `.env.example` (default "Acme Console"), the logo in `resources/views/components/app-logo-icon.blade.php`, and the colours in `resources/css/app.css`.
- The repository links in `resources/views/layouts/app/sidebar.blade.php` and `resources/views/welcome.blade.php`.
- Pick your database. See [Choosing a database](#choosing-a-database).
- Replace the copyright holder in `LICENSE` (or choose another license), and rewrite this README for your project.

**4. Remove what you don't need.** The demo is there to show the patterns end to end. Keep the parts you'll build on and delete the rest:

- **Users module:** to drop it, delete `resources/views/pages/users/`, `resources/views/components/users/`, `app/Livewire/Forms/UserForm.php`, `UserPolicy`, `tests/Feature/Users/`, the `users.index` route and its sidebar item. Keep the `Role` and `UserStatus` enums if you still want roles.
- **Demo data:** the 47 placeholder users come from `database/seeders/DatabaseSeeder.php`. Trim it to the accounts you need for development.
- **Welcome page:** `resources/views/welcome.blade.php` describes this template. Replace it with your own landing page, or redirect `/` to the dashboard in `routes/web.php`.

**5. Turn on the repository settings.** In **Settings → Code security**, enable Dependabot alerts and private vulnerability reporting (`SECURITY.md` points reporters there). CI in `.github/workflows/` runs on your first push with no secrets required. Add branch protection on `main` that requires it to pass.

## Getting started

Requirements: **PHP 8.3+** (8.5 recommended), **Composer 2**, **Node.js 22+** (for the Vite asset build only).

```bash
composer setup   # install, create .env, generate key, migrate, seed demo data, build assets
composer dev     # Laravel server, queue worker, logs and Vite, together
```

Open <http://localhost:8000> and sign in with the demo account:

| Email              | Password   | Role  |
| ------------------ | ---------- | ----- |
| `demo@example.com` | `password` | Admin |

The seeder adds 47 more placeholder users with mixed roles and statuses, spread over the last six months so the dashboard has data. Reset everything with `php artisan migrate:fresh --seed`.

> **Prefer Docker?** `docker compose up --build` starts the app on <http://localhost:8080> with PostgreSQL and the same demo data. See [Docker](#docker).

## Choosing a database

The template is **database-agnostic**. Migrations use only portable column types (roles and statuses are strings validated by PHP enums, not native DB enums), and CI runs every test against SQLite, PostgreSQL and MySQL.

| Database             | Setup                                                                                                                                              |
| -------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------- |
| **SQLite** (default) | Nothing to do. The file is `database/database.sqlite`.                                                                                             |
| **PostgreSQL**       | `docker compose up -d pgsql`, then set `DB_CONNECTION=pgsql`, `DB_PORT=5432`, `DB_DATABASE=laravel`, `DB_USERNAME=laravel`, `DB_PASSWORD=secret` |
| **MySQL / MariaDB**  | `docker compose --profile mysql up -d mysql`, then set `DB_CONNECTION=mysql`, `DB_PORT=3306` and the same credentials                             |

Uncomment `DB_HOST` in `.env` as well, then run `php artisan migrate --seed`. Any database Laravel supports works the same way.

## Scripts

| Command             | What it does                                                                          |
| ------------------- | ------------------------------------------------------------------------------------- |
| `composer setup`    | First-time setup: dependencies, `.env`, key, migrations, demo data and an asset build |
| `composer dev`      | Development server, queue worker, log tail and Vite with hot reload                   |
| `composer test`     | Pint, PHPStan and the Pest suite                                                       |
| `composer lint`     | Format PHP with Pint                                                                   |
| `npm run build`     | Production asset build                                                                 |

## Project structure

```text
app/
├── Enums/                    Role, UserStatus: string-backed, with labels and rules
├── Http/Middleware/          SecurityHeaders (CSP), EnsureUserIsActive
├── Livewire/Forms/UserForm   Validation and saving for the users page
├── Models/User.php           Enum casts, query scope for filters, model attributes
├── Policies/UserPolicy.php   Who can create, update, delete and change access
├── Providers/                Strict models, HTTPS URLs, Fortify sign-in rules
└── Support/DashboardMetrics  Portable dashboard aggregates
database/
├── factories/UserFactory.php    States: admin(), editor(), invited(), suspended(), …
└── seeders/DatabaseSeeder.php   Placeholder data only, idempotent
resources/views/
├── pages/                    Full-page Livewire components: ⚡dashboard, users/⚡index, auth/*, settings/*
├── components/               ⚡dashboard-metrics (lazy), users/* badges, app shell pieces
├── layouts/                  App (sidebar) and auth layouts
├── flux/                     Published Flux overrides
└── welcome.blade.php         Landing page
tests/
├── Feature/                  Users, dashboard, account status, headers, auth and settings
└── ArchTest.php              Architecture rules enforced on every run
docker/                       Entrypoint and PHP production settings
```

## Architecture

**Blade on the server, Livewire for interactivity.** Pages are Livewire 4 single-file components: the PHP class and its Blade template live in one `⚡` file. Clicking, typing and submitting call methods on the server, and Livewire patches the DOM with the result. There is no API, client-side router or JavaScript state to keep in sync, and Alpine.js covers the purely client-side touches.

**One place per concern.**

| Concern       | Lives in                                                       |
| ------------- | -------------------------------------------------------------- |
| Who may act   | `UserPolicy`, checked with `$this->authorize()` in each action |
| What is valid | `UserForm` (a Livewire form object)                            |
| What is shown | Blade, with `@can` hiding actions the user can't take          |
| Query filters | The `filter` scope on `User`                                   |

**Never trust the browser.** Every Livewire action re-checks the policy, because any public method can be called with any arguments. The user being edited is `#[Locked]`, so it can't be swapped client-side. Tests call the actions directly to prove both.

**Lazy loading.** The dashboard page renders its header at once, and `<livewire:dashboard-metrics lazy />` fetches the aggregates in a follow-up request, showing Flux skeletons meanwhile.

**URL as state.** The users list binds its search and filters to the query string with `#[Url]`. Results are shareable and survive a refresh, and the back button works. Unknown values from a hand-edited URL are ignored.

## Users and roles

| Action                        | Admin | Editor | Viewer |
| ----------------------------- | :---: | :----: | :----: |
| View dashboard and users      |   ✓   |   ✓    |   ✓    |
| Add, edit and delete users    |   ✓   |        |        |
| Change a user's role/status   |   ✓   |        |        |

- Admins can't change their own role or status, or delete themselves, so a workspace can't lock itself out.
- New registrations get the least privileged role (`Viewer`). Users added by an admin start as `Invited` and set their password with the reset link.
- `Invited` and `Suspended` users can't sign in. Suspending someone ends their current session on their next request.

## Security

- **Content Security Policy** with a fresh nonce per request (`SecurityHeaders` middleware). Vite and Livewire add it to their tags automatically, and the `@fluxScripts` / `@fluxAppearance` directives receive it explicitly. A test checks that **every** `<script>` on the page carries the nonce. The policy is enforced outside the `local` environment, so Vite's dev server keeps working.
- **About `'unsafe-eval'`:** Alpine.js evaluates `x-*` attribute expressions with the `Function` constructor, so the policy allows it. Injected `<script>` tags are still blocked by the nonce, and Blade escapes all output, so user data can't add Alpine attributes. For an eval-free policy, set `'csp_safe' => true` in `config/livewire.php` and move inline `x-data` objects into `Alpine.data()` registrations.
- Livewire caches rendered `@assets` in static state. The middleware resets it per request, so long-running workers (Octane, FrankenPHP worker mode) never serve a previous request's nonce.
- `Strict-Transport-Security` on HTTPS, `X-Frame-Options: DENY`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, `Cross-Origin-Opener-Policy`.
- Passwords are hashed with bcrypt, sign-in and two-factor attempts are rate limited (Fortify), and sessions are regenerated on login.
- `Model::shouldBeStrict()` outside production catches lazy loading and unknown attributes early. Destructive database commands are blocked in production.
- Architecture tests forbid `dd()`/`dump()`, `env()` outside config, and insecure functions such as `eval` and `md5`.

Behind a load balancer, add `$middleware->trustProxies(at: [...])` in `bootstrap/app.php` so HTTPS and client IPs are detected correctly.

## Testing and quality

```bash
composer test        # Pint + PHPStan + Pest
```

Livewire components are tested with `Livewire::test()`, which calls their actions the way the browser does, including the forbidden ones. Feature tests run against an in-memory SQLite database by default. To run them against another database, override the connection:

```bash
DB_CONNECTION=pgsql DB_HOST=127.0.0.1 DB_DATABASE=testing DB_USERNAME=laravel DB_PASSWORD=secret php artisan test
```

GitHub Actions (`.github/workflows/ci.yml`) runs:

1. **Lint & static analysis:** Pint, PHPStan level 8, an asset build, `composer audit`, `npm audit`
2. **Tests:** the Pest suite in parallel on **SQLite, PostgreSQL 18 and MySQL 8.4**
3. **Docker:** builds the production image and smoke-tests `/up`

Dependabot keeps Composer, npm, Docker and Actions dependencies current, grouping minor and patch updates.

## Docker

```bash
docker compose up --build            # app :8080, PostgreSQL :5432, Mailpit :8025
```

- `APP_KEY` is read from your `.env`. Run `php artisan key:generate` first if you haven't.
- On start, the container caches config, routes and views, runs migrations, and seeds demo data once (`SEED_DEMO_DATA=true` in `compose.yaml`).
- Ports are configurable: `APP_PORT`, `FORWARD_DB_PORT`, `FORWARD_MAILPIT_PORT`, `FORWARD_MAILPIT_DASHBOARD_PORT`.

The `Dockerfile` is multi-stage:

1. **build:** Composer and npm dependencies and the Vite build. Layers are cached by lock file.
2. **app:** [FrankenPHP](https://frankenphp.dev) serving `public/` with production `php.ini` and OPcache. It runs as `www-data` on port 8080 and includes a health check.

`docker build .` produces a production image without dev dependencies. Compose builds with `COMPOSER_DEV=true` so the demo seeder can use Faker.

## Deployment

1. Set the environment: `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY`, `APP_URL` (use `https://`), and the `DB_*`, `MAIL_*`, `SESSION_*` and `CACHE_*` values.
2. Run migrations with `php artisan migrate --force`, or set `RUN_MIGRATIONS=true` on the container.
3. Run a queue worker (`php artisan queue:work`) and the scheduler (`php artisan schedule:work`) as separate processes from the same image.
4. Don't run the seeder in production unless you want the demo data.

The image runs anywhere containers do (Fly.io, Railway, Render, ECS, Kubernetes). Laravel Cloud and Forge can deploy the repository directly.

## Customising the template

1. **Rename** `APP_NAME` in `.env` (default "Acme Console") and replace `resources/views/components/app-logo-icon.blade.php`.
2. **Add a page:** `php artisan make:livewire pages::projects.index`, register it with `Route::livewire('projects', 'pages::projects.index')`, and add a sidebar item. Add a policy and authorise every action.
3. **Add a role or status:** add a case to the enum. Validation, filters and the badges read from it.
4. **Remove demo features:** see [Use this template](#use-this-template), step 4.

## License

[MIT](./LICENSE)
