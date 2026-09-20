# r/anime Awards Web

This repository contains the web application used to run the r/anime Awards. It covers the public awards site, juror applications, nomination and final voting, published results, feedback, and an administrative dashboard.

## Technology overview

The application is a [Laravel 11](https://laravel.com/docs/11.x) project running on [PHP 8.2+](https://www.php.net/docs.php). Its server-rendered pages use [Blade](https://laravel.com/docs/11.x/blade), while interactive voting screens use [Livewire 3](https://livewire.laravel.com/docs/3.x/quickstart) and [Alpine.js](https://alpinejs.dev/start-here).

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
| `database/seeders` | Initial site options used by a fresh database. |
| `tests/Feature`, `tests/Unit` | HTTP-level and isolated PHPUnit tests. |
| `public` | Web entry point and static images/fonts. Generated Vite assets are written to `public/build`. |

The main public route definitions are in `routes/web.php`. `/` serves the home page, `/participate/*` contains application and voting flows, `/results` serves the latest published results, and `/dashboard` is the Filament panel. Many domain rules and current awards settings are represented by records in the database rather than static configuration files.

## Local setup

Prerequisites:

- PHP 8.2 or newer, including the `intl` and SQLite extensions
- [Composer](https://getcomposer.org/doc/00-intro.md)
- [Node.js and npm](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm)

Install the project and initialize a local SQLite database:

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm run build
```

The final build is needed before first use because the application and admin panel expect a Vite manifest in `public/build`.

For Reddit sign-in, create a Reddit OAuth application and replace the placeholder `REDDIT_CLIENT_ID`, `REDDIT_CLIENT_SECRET`, and `REDDIT_REDIRECT_URI` values in `.env`. Keep `APP_URL` and the callback URL consistent with the hostname used to serve the application. The placeholder Cloudflare Turnstile keys can remain in local development while `CFTURNSTILE_ENABLE=false`.

## Development loop

Run the backend and Vite development server in separate terminals:

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

`migrate:fresh` deletes all data in the selected database, so only use it against a disposable local database. The PHPUnit configuration currently inherits the configured database connection; confirm that `.env` points to the local SQLite file before running database-writing tests.

There are currently no JavaScript test or lint scripts in `package.json`. For most changes, a good pre-push check is:

```bash
php artisan test
./vendor/bin/pint --test
npm run build
```