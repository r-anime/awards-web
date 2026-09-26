#!/usr/bin/env bash

set -euo pipefail

check_only=false

if [[ ${1:-} == "--check" ]]; then
    check_only=true
elif [[ $# -gt 0 ]]; then
    echo "Usage: $0 [--check]" >&2
    exit 2
fi

has_php=false
has_php_intl=false
has_php_sqlite=false
has_sqlite_cli=false
has_composer=false
has_node=false
has_npm=false

if command -v php >/dev/null 2>&1 && php -r 'exit(version_compare(PHP_VERSION, "8.2.0", ">=") ? 0 : 1);'; then
    has_php=true
    php_modules=$(php -m)
    grep -qi '^intl$' <<<"$php_modules" && has_php_intl=true
    grep -Eqi '^(pdo_sqlite|sqlite3)$' <<<"$php_modules" && has_php_sqlite=true
fi

command -v composer >/dev/null 2>&1 && has_composer=true
command -v sqlite3 >/dev/null 2>&1 && has_sqlite_cli=true

if command -v node >/dev/null 2>&1 && node -e 'process.exit(Number(process.versions.node.split(".")[0]) >= 18 ? 0 : 1)'; then
    has_node=true
fi

command -v npm >/dev/null 2>&1 && has_npm=true

missing=()
$has_php || missing+=("PHP 8.2+")
$has_php_intl || missing+=("PHP intl extension")
$has_php_sqlite || missing+=("PHP SQLite extension")
$has_sqlite_cli || missing+=("sqlite3")
$has_composer || missing+=("Composer")
$has_node || missing+=("Node.js 18+")
$has_npm || missing+=("npm")

if [[ ${#missing[@]} -eq 0 ]]; then
    echo "All prerequisites are installed."
    php --version | head -n 1
    composer --version
    node --version
    npm --version
    exit 0
fi

echo "Missing or outdated prerequisites: ${missing[*]}"

if $check_only; then
    exit 1
fi

install_macos() {
    if ! command -v brew >/dev/null 2>&1; then
        echo "Installing Homebrew..."
        /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

        if [[ -x /opt/homebrew/bin/brew ]]; then
            eval "$(/opt/homebrew/bin/brew shellenv)"
        elif [[ -x /usr/local/bin/brew ]]; then
            eval "$(/usr/local/bin/brew shellenv)"
        else
            echo "Homebrew was installed but is not available in PATH." >&2
            exit 1
        fi
    fi

    packages=()
    if ! $has_php || ! $has_php_intl || ! $has_php_sqlite; then
        packages+=("php")
    fi
    $has_sqlite_cli || packages+=("sqlite")
    $has_composer || packages+=("composer")
    if ! $has_node || ! $has_npm; then
        packages+=("node")
    fi

    brew install "${packages[@]}"
}

install_debian() {
    if [[ $EUID -eq 0 ]]; then
        sudo_command=()
    elif command -v sudo >/dev/null 2>&1; then
        sudo_command=(sudo)
    else
        echo "Installing packages requires root access or sudo." >&2
        exit 1
    fi

    "${sudo_command[@]}" apt-get update

    packages=()
    if ! $has_php || ! $has_php_intl || ! $has_php_sqlite; then
        packages+=(php-cli php-intl php-sqlite3 php-mbstring php-xml php-curl unzip)
    fi
    $has_sqlite_cli || packages+=(sqlite3)
    $has_composer || packages+=(composer)

    if [[ ${#packages[@]} -gt 0 ]]; then
        "${sudo_command[@]}" apt-get install -y "${packages[@]}"
    fi

    if ! $has_node || ! $has_npm; then
        "${sudo_command[@]}" apt-get install -y curl ca-certificates
        if [[ ${#sudo_command[@]} -eq 0 ]]; then
            curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
        else
            curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
        fi
        "${sudo_command[@]}" apt-get install -y nodejs
    fi
}

case "$(uname -s)" in
    Darwin)
        install_macos
        ;;
    Linux)
        if command -v apt-get >/dev/null 2>&1; then
            install_debian
        else
            echo "Automatic installation currently supports Debian, Ubuntu, and WSL distributions that provide apt-get." >&2
            exit 1
        fi
        ;;
    *)
        echo "Automatic installation currently supports macOS, Debian, Ubuntu, and Windows through WSL." >&2
        exit 1
        ;;
esac

echo "Verifying installed prerequisites..."

errors=0
if ! command -v php >/dev/null 2>&1 || ! php -r 'exit(version_compare(PHP_VERSION, "8.2.0", ">=") ? 0 : 1);'; then
    echo "PHP 8.2 or newer is not available." >&2
    errors=1
fi
if ! php -m | grep -qi '^intl$'; then
    echo "The PHP intl extension is not available." >&2
    errors=1
fi
if ! php -m | grep -Eqi '^(pdo_sqlite|sqlite3)$'; then
    echo "A PHP SQLite extension is not available." >&2
    errors=1
fi
if ! command -v sqlite3 >/dev/null 2>&1; then
    echo "The sqlite3 command is not available." >&2
    errors=1
fi
if ! command -v composer >/dev/null 2>&1; then
    echo "Composer is not available." >&2
    errors=1
fi
if ! command -v node >/dev/null 2>&1 || ! node -e 'process.exit(Number(process.versions.node.split(".")[0]) >= 18 ? 0 : 1)'; then
    echo "Node.js 18 or newer is not available." >&2
    errors=1
fi
if ! command -v npm >/dev/null 2>&1; then
    echo "npm is not available." >&2
    errors=1
fi

if [[ $errors -ne 0 ]]; then
    echo "Installation did not provide every required prerequisite." >&2
    exit 1
fi

echo "All prerequisites are installed."
php --version | head -n 1
composer --version
node --version
npm --version
