#!/usr/bin/env bash

set -euo pipefail

force=false

if [[ ${1:-} == "--force" ]]; then
    force=true
elif [[ $# -gt 0 ]]; then
    echo "Usage: $0 [--force]" >&2
    exit 2
fi

repository_root=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
completion_marker="$repository_root/.setup-complete"

cd "$repository_root"

if [[ -f "$completion_marker" ]] && ! $force; then
    echo "Project setup is already complete. Use --force to start from scratch."
    exit 0
fi

if ! bash scripts/install-prerequisites.sh --check; then
    echo "Install the missing prerequisites with: bash scripts/install-prerequisites.sh" >&2
    exit 1
fi

if $force; then
    echo "Removing generated setup files..."
    rm -rf \
        "$repository_root/vendor" \
        "$repository_root/node_modules" \
        "$repository_root/public/build"
    rm -f \
        "$repository_root/.env" \
        "$repository_root/database/database.sqlite" \
        "$completion_marker"
fi

echo "Installing PHP dependencies..."
composer install

echo "Installing frontend dependencies..."
npm ci

if [[ ! -f .env ]]; then
    cp .env.example .env
fi

if ! grep -Eq '^APP_KEY=.+$' .env; then
    echo "Generating the application key..."
    php artisan key:generate
fi

mkdir -p database
touch database/database.sqlite

echo "Migrating the local database..."
php artisan migrate

echo "Seeding site options..."
php artisan db:seed --class=OptionSeeder

echo "Seeding bundled development results..."
php artisan db:seed --class=DevelopmentResultsSeeder

echo "Seeding bundled development acknowledgements..."
php artisan db:seed --class=DevelopmentAcknowledgementsSeeder

echo "Building frontend assets..."
npm run build

touch "$completion_marker"
echo "Project setup is complete."
