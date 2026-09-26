# r/anime Awards Web

This repository contains the web application used to run the r/anime Awards. It covers the public awards site, juror applications, nomination and final voting, published results, feedback, and an administrative dashboard.

## Technology overview

The application is a [Laravel 11](https://laravel.com/docs/11.x) project running on [PHP 8.2 through 8.4](https://www.php.net/docs.php). Its server-rendered pages use [Blade](https://laravel.com/docs/11.x/blade), while interactive voting screens use [Livewire 3](https://livewire.laravel.com/docs/3.x/quickstart) and [Alpine.js](https://alpinejs.dev/start-here).

Results and acknowledgement pages use [Inertia.js](https://inertiajs.com/) with [Vue 3](https://vuejs.org/guide/introduction.html). Styles are written in CSS and [Sass](https://sass-lang.com/documentation/), with [Bulma](https://bulma.io/documentation/) used by parts of the interface. [Vite](https://vite.dev/guide/) builds and serves the frontend assets through the [Laravel Vite plugin](https://laravel.com/docs/11.x/vite).

The administrative UI is built with [Filament 4](https://filamentphp.com/docs/4.x). Authentication uses [Laravel Socialite](https://laravel.com/docs/11.x/socialite) and the [Reddit Socialite provider](https://socialiteproviders.com/Reddit/). Data access uses Laravel's [Eloquent ORM](https://laravel.com/docs/11.x/eloquent) and [database migrations](https://laravel.com/docs/11.x/migrations); local development defaults to SQLite.

Tests run with [PHPUnit 11](https://docs.phpunit.de/en/11.5/), and PHP formatting is enforced by [Laravel Pint](https://laravel.com/docs/11.x/pint).

## Repository layout

| Path | Purpose |
| --- | --- |
| `app/Http/Controllers` | Request handling for applications, authentication, feedback, results, and related endpoints. |
| `app/Http/Middleware` | Access rules, including voting-window and authorization checks. |
| `app/Livewire` | Stateful nomination and final-voting components. |
| `app/Filament/Admin` | Admin resources, pages, and widgets for managing awards data. |
| `app/Models` | Eloquent models for users, categories, entries, votes, results, applications, and site configuration. |
| `app/Services` | Domain services, including result assembly. |
| `app/Jobs` | Queueable work such as image downloads and audit webhooks. |
| `app/Console/Commands` | Artisan import commands for historical and third-party awards data. |
| `routes/web.php` | Public, authenticated voting, results, and feedback routes. |
| `resources/views` | Blade templates and Livewire views. |
| `resources/js/Pages` | Vue pages rendered through Inertia. |
| `resources/js/Components` | Reusable Vue components. |
| `resources/css`, `resources/scss` | Frontend styles compiled by Vite. |
| `database/migrations` | Database schema history. |
| `database/seeders` | Initial site options and offline development archive data. |
| `tests/Feature`, `tests/Unit` | HTTP-level and isolated PHPUnit tests. |
| `public` | Web entry point and static images/fonts. Generated Vite assets are written to `public/build`. |

The main public route definitions are in `routes/web.php`. `/` serves the home page, `/participate/*` contains application and voting flows, `/results` serves the latest published results, and `/dashboard` is the Filament panel. Many domain rules and current awards settings are represented by records in the database rather than static configuration files.

## Local setup

Prerequisites:

- PHP 8.2 through 8.4, including the `intl` and SQLite extensions
- [Composer](https://getcomposer.org/doc/00-intro.md)
- [Node.js and npm](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm)

On macOS, Ubuntu, Debian, or Windows through WSL, install any missing prerequisites from the repository root:

```bash
bash scripts/install-prerequisites.sh
```

The script checks versions and required PHP extensions, installs anything missing, and verifies the result. On macOS it uses [Homebrew](https://brew.sh/), installing and linking PHP 8.4 and installing Homebrew first if necessary. On Ubuntu, Debian, and WSL it uses `apt-get` and NodeSource. PHP 8.5 is not supported by Laravel 11. Restart any running PHP development server after the script switches PHP versions. Use `bash scripts/install-prerequisites.sh --check` to check without installing anything.

If you use `nvm`, running `nvm install` in this repository installs the Node.js version specified by `.nvmrc`; npm is included with Node.js.

Run the project setup:

```bash
bash scripts/setup.sh
```

The setup script installs project dependencies, creates `.env`, generates the application key, initializes and seeds the local SQLite database, and builds the frontend. Laravel loads the committed SQLite schema snapshot before running newer migrations, so historical MySQL-specific migrations do not need to be changed. Later runs are a no-op after setup completes successfully.

Use `bash scripts/setup.sh --force` to delete `.env`, the local SQLite database and its data, installed dependencies, and built assets, then repeat setup from scratch.

Optional post-setup commands:

| Command | Purpose |
| --- | --- |
| `php artisan storage:link` | Expose downloaded entry images through `public/storage`. |
| `php artisan app:import-archive 2024 --anilist-only` | Enrich bundled 2024 results with AniList metadata and images without requiring an API key. |
| `php artisan app:import-archive 2024` | Enrich bundled results using AniList and the slower AnimeThemes import. |
| `php artisan app:import-anilist 2024` | Import the full AniList catalog for voting data; add `--chars`, `--chars --vas`, or `--queue` as needed. |
| `php artisan migrate:fresh --seed` | Delete local database data, rebuild the schema, and reload the default seed data. |
| `php artisan schema:dump --database=sqlite` | Regenerate `database/schema/sqlite-schema.sql` from a fully migrated SQLite database after schema changes. |

For Reddit sign-in, create a Reddit OAuth application and replace the placeholder `REDDIT_CLIENT_ID`, `REDDIT_CLIENT_SECRET`, and `REDDIT_REDIRECT_URI` values in `.env`. Keep `APP_URL` and the callback URL consistent with the hostname used to serve the application. The placeholder Cloudflare Turnstile keys can remain in local development while `CFTURNSTILE_ENABLE=false`.

## Development loop

Start the Laravel server, queue worker, and Vite development server together:

```bash
npm run dev:all
```

They can also be run separately:

```bash
# Terminal 1: Laravel application
php artisan serve

# Terminal 2: frontend assets with hot reload
npm run dev
```

By default, `php artisan serve` makes the site available at <http://127.0.0.1:8000>. If a change dispatches queued jobs, run a third process because the local queue connection is database-backed:

```bash
php artisan queue:work
```

The public home, results, acknowledgements, feedback, and credits routes work with the local seed data. Application, nomination, final-vote, and admin workflows require a Reddit OAuth application and may also depend on active voting dates and additional awards data configured through the dashboard.

Typical commands while making a change are:

```bash
# Run all tests
php artisan test

# Run one test file or a matching test name
php artisan test tests/Feature/ExampleTest.php
php artisan test --filter=application_returns_a_successful_response

# Check formatting without changing files
./vendor/bin/pint --test

# Apply PHP formatting
./vendor/bin/pint

# Verify a production frontend build
npm run build

# Rebuild the local database from all migrations and seed defaults
php artisan migrate:fresh --seed

# Clear framework caches after changing configuration or routes
php artisan optimize:clear
```

`migrate:fresh` deletes all data in the selected database, so only use it against a disposable local database. PHPUnit uses an isolated in-memory SQLite database and does not modify the database configured in `.env`.

There are currently no JavaScript test or lint scripts in `package.json`. For most changes, a good pre-push check is:

```bash
php artisan test
./vendor/bin/pint --test
npm run build
```