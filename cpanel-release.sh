#!/usr/bin/env bash

set -Eeuo pipefail
umask 022

commit=0b31c50ea6cb89a7fa5b33f0ec25c75c917c698a
origin=https://kliq.ma
site="$HOME/kliq-site"
web="$HOME/public_html"
private="$site/private"
tools="$site/tools"

mkdir -p "$site/releases" "$site/media" "$private" "$tools"
chmod 700 "$private" "$tools"
exec 9>"$private/deploy.lock"
flock -n 9
exec >"$private/deploy.log" 2>&1

printf 'Preparing KLIQ commit %s\n' "$commit"
[[ -d "$web" && ! -L "$web" && ! -L "$web/.htaccess" ]]
if [[ -f "$private/live-commit" && -f "$web/index.php" ]] && grep -Fxq "$commit" "$private/live-commit"; then
    printf 'This commit is already live.\n'
    exit 0
fi

php=/opt/cpanel/ea-php84/root/usr/bin/php
"$php" -r 'if (PHP_VERSION_ID < 80401 || !extension_loaded("pdo_mysql")) { exit(1); } echo "PHP ", PHP_VERSION, PHP_EOL;'

release=
stage=$(mktemp -d "$private/publish.XXXXXXXX")
publish_started=0
published=0
htaccess_installed=0
installed=()
rollback() {
    code=$?
    if (( code != 0 )); then
        if (( publish_started && ! published )); then
            set +e
            for name in "${installed[@]}"; do
                mv -- "$web/$name" "$stage/$name"
            done
            if (( htaccess_installed )); then
                mv -- "$web/.htaccess" "$stage/failed.htaccess"
                if [[ -f "$stage/original.htaccess" ]]; then
                    cp -p -- "$stage/original.htaccess" "$web/.htaccess"
                fi
            fi
            printf 'Restored the previous web directory.\n'
        fi
        printf 'Deployment stopped with exit status %s.\n' "$code"
    fi
}
trap rollback EXIT

for staged in "$site/releases/0b31c50."*; do
    if [[ -s "$staged/vendor/autoload.php" && -f "$staged/composer.lock" ]]; then
        release=$staged
    fi
done

if [[ -n "$release" ]]; then
    git show "$commit:composer.lock" | cmp -s "$release/composer.lock" -
    git show "$commit:package-lock.json" | cmp -s "$release/package-lock.json" -
    printf 'Reusing the staged PHP release.\n'
else
    release=$(mktemp -d "$site/releases/0b31c50.XXXXXXXX")
    git archive "$commit" app artisan bootstrap composer.json composer.lock config database lang package.json package-lock.json public resources routes storage vite.config.js .npmrc |
        tar -x -C "$release"
fi
cd "$release"
fetch() {
    curl --fail --silent --show-error --location --retry 2 --connect-timeout 12 --max-time 120 "$@"
}

if [[ ! -f "$tools/composer.phar" ]]; then
    printf 'Installing Composer in the hosting account.\n'
    fetch -o "$stage/composer.phar" https://getcomposer.org/download/2.11.2/composer.phar
    printf '%s  %s\n' dd158c0afd41d15fac89e1b4e47b5d42911fe316c3687cd5edac98319b25d028 "$stage/composer.phar" |
        sha256sum --check --status
    mv -- "$stage/composer.phar" "$tools/composer.phar"
fi
if [[ ! -s vendor/autoload.php ]]; then
    printf 'Installing locked PHP dependencies.\n'
    "$php" "$tools/composer.phar" install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress
fi
"$php" "$tools/composer.phar" check-platform-reqs --no-dev --no-interaction

printf 'Installing the prebuilt assets for %s.\n' "$commit"
tr -d '\r' <"$private/build-0b31c50.b64.txt" | tr '_-' '/+' | base64 --decode >"$stage/build-assets.tar.gz"
printf '%s  %s\n' 85282fffe388ae4965907a0d51816aca8631268ebe5633d20e7908fb59bdd6fa "$stage/build-assets.tar.gz" |
    sha256sum --check --status
tar -xzf "$stage/build-assets.tar.gz" -C "$release/public"
[[ -s public/build/manifest.json && -s public/build/fonts-manifest.json ]]

env_file="$private/application.env"
if [[ ! -s "$env_file" || -L "$env_file" ]]; then
    printf 'The private cPanel database configuration is missing.\n' >&2
    exit 1
fi
chmod 600 "$env_file"
printf 'Using the dedicated database configured in cPanel.\n'
if [[ ! -L .env ]]; then
    ln -s "$env_file" .env
fi
if [[ ! -L storage/app/public ]]; then
    mv storage/app/public storage/app/public-template
    ln -s "$site/media" storage/app/public
fi
if [[ ! -L public/storage ]]; then
    ln -s "$site/media" public/storage
fi
"$php" artisan migrate --force --no-interaction
"$php" artisan db:seed --force --no-interaction
"$php" artisan optimize --no-interaction

for route in /up /en /fr /ar /en/projects /admin/login; do
    "$php" -r '
        require "vendor/autoload.php";
        $app = require "bootstrap/app.php";
        $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
        $request = Illuminate\Http\Request::create($argv[1]);
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);
        printf("%s %d\n", $argv[1], $response->getStatusCode());
        exit($response->isSuccessful() ? 0 : 1);
    ' "$origin$route"
done

printf 'Preparing the public files for activation.\n'
mkdir "$stage/files"
cp -a -- public/. "$stage/files/"
printf '<?php\n\nrequire __DIR__ . %s;\n' "'/../kliq-site/releases/${release##*/}/public/index.php'" >"$stage/files/index.php"

for asset in "$stage/files/"*; do
    name=${asset##*/}
    if [[ -e "$web/$name" || -L "$web/$name" ]]; then
        printf 'The web root already contains %s. Deployment stopped before replacing it.\n' "$name" >&2
        exit 1
    fi
done

if [[ -f "$web/.htaccess" ]]; then
    cp -p -- "$web/.htaccess" "$stage/original.htaccess"
fi
{
    if [[ -f "$stage/original.htaccess" ]]; then
        cat "$stage/original.htaccess"
        printf '\n\n'
    fi
    cat <<'APACHE'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} !=on
    RewriteRule ^ https://kliq.ma%{REQUEST_URI} [R=301,L,NE]
</IfModule>

APACHE
    cat public/.htaccess
} >"$stage/new.htaccess"

# Install the entry point last. All files moved here can be moved back on failure.
printf 'Activating KLIQ at %s.\n' "$origin"
publish_started=1
for asset in "$stage/files/"*; do
    name=${asset##*/}
    if [[ "$name" != index.php ]]; then
        mv -- "$asset" "$web/$name"
        installed+=("$name")
    fi
done
mv -- "$stage/new.htaccess" "$web/.htaccess"
htaccess_installed=1
mv -- "$stage/files/index.php" "$web/index.php"
installed+=(index.php)

for route in /up /fr /ar /en/projects /admin/login; do
    fetch -o /dev/null -H 'Cache-Control: no-cache' "$origin$route?release=$commit"
    printf 'Live check passed for %s.\n' "$route"
done
printf '%s\n' "$commit" >"$private/live-commit"
published=1
printf 'KLIQ deployment complete.\n'
