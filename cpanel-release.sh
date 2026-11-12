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
uapi=/usr/local/cpanel/bin/uapi
"$php" -r 'if (PHP_VERSION_ID < 80401 || !extension_loaded("pdo_mysql")) { exit(1); } echo "PHP ", PHP_VERSION, PHP_EOL;'
if [[ ! -x "$uapi" ]]; then
    uapi=$(command -v uapi)
fi

release=$(mktemp -d "$site/releases/0b31c50.XXXXXXXX")
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

git archive "$commit" app artisan bootstrap composer.json composer.lock config database lang package.json package-lock.json public resources routes storage vite.config.js .npmrc |
    tar -x -C "$release"
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
printf 'Installing locked PHP dependencies.\n'
"$php" "$tools/composer.phar" install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress
"$php" "$tools/composer.phar" check-platform-reqs --no-dev --no-interaction

node_works() {
    command -v npm >/dev/null && node -e 'const [major,minor] = process.versions.node.split(".").map(Number); process.exit(major >= 24 || major === 22 && minor >= 12 ? 0 : 1)' 2>/dev/null
}
if ! node_works; then
    for node_bin in /opt/alt/alt-nodejs24/root/usr/bin /opt/alt/alt-nodejs22/root/usr/bin; do
        if [[ -x "$node_bin/node" ]] && PATH="$node_bin:$PATH" node_works; then
            export PATH="$node_bin:$PATH"
            break
        fi
    done
fi
if ! node_works; then
    node_version=v24.22.1
    node_dist="node-$node_version-linux-x64"
    if [[ ! -x "$tools/$node_dist/bin/node" ]]; then
        printf 'Installing Node.js in the hosting account.\n'
        [[ $(uname -m) == x86_64 ]]
        node_url="https://nodejs.org/dist/$node_version"
        fetch -o "$stage/$node_dist.tar.xz" "$node_url/$node_dist.tar.xz"
        fetch -o "$stage/SHASUMS256.txt" "$node_url/SHASUMS256.txt"
        (cd "$stage"; awk -v file="$node_dist.tar.xz" '$2 == file {print}' SHASUMS256.txt | sha256sum --check --status)
        tar -xJf "$stage/$node_dist.tar.xz" -C "$tools"
    fi
    export PATH="$tools/$node_dist/bin:$PATH"
fi
printf 'Building the production assets with Node %s.\n' "$(node --version)"
npm ci --prefer-offline --ignore-scripts --no-audit --no-fund
RAYON_NUM_THREADS=1 NODE_USE_ENV_PROXY=1 npm run build
[[ -s public/build/manifest.json && -s public/build/fonts-manifest.json ]]

mysql_api() {
    result=$("$uapi" --output=json Mysql "$@")
    if ! printf '%s' "$result" | "$php" -r '$r = json_decode(stream_get_contents(STDIN), true); exit(($r["result"]["status"] ?? 0) == 1 ? 0 : 1);'; then
        printf 'cPanel database action %s failed.\n' "$1" >&2
        return 1
    fi
    printf '%s\n' "$result"
}
has_record() {
    "$php" -r '$r = json_decode(stream_get_contents(STDIN), true); foreach (($r["result"]["data"] ?? []) as $row) { if (($row[$argv[1]] ?? null) === $argv[2]) { exit(0); } } exit(1);' "$1" "$2"
}

db="$(id -un)_kliqsite"
database_list=$(mysql_api list_databases)
user_list=$(mysql_api list_users)
if [[ ! -f "$private/.env" ]]; then
    if has_record database "$db" <<<"$database_list" || has_record user "$db" <<<"$user_list"; then
        printf 'A database or database user with the release name already exists.\n' >&2
        exit 1
    fi
    app_key=$(openssl rand -base64 32)
    db_password=$(openssl rand -hex 32)
    env_temp=$(mktemp "$private/env.XXXXXXXX")
    cat >"$env_temp" <<ENV
APP_NAME=KLIQ
APP_ENV=production
APP_KEY=base64:$app_key
APP_DEBUG=false
APP_URL=$origin
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
LOG_CHANNEL=daily
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=$db
DB_USERNAME=$db
DB_PASSWORD=$db_password
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database
MAIL_MAILER=log
MEDIA_DISK=public
ENV
    mv -- "$env_temp" "$private/.env"
fi
db_password=$(sed -n 's/^DB_PASSWORD=//p' "$private/.env")
[[ "$db_password" =~ ^[0-9a-f]{64}$ ]]
printf 'Initializing the dedicated KLIQ database.\n'
if ! has_record database "$db" <<<"$database_list"; then
    mysql_api create_database name="$db" >/dev/null
fi
if ! has_record user "$db" <<<"$user_list"; then
    mysql_api create_user name="$db" password="$db_password" >/dev/null
fi
unset db_password app_key
mysql_api set_privileges_on_database user="$db" database="$db" privileges='ALL PRIVILEGES' >/dev/null

ln -s "$private/.env" .env
mv storage/app/public storage/app/public-template
ln -s "$site/media" storage/app/public
ln -s "$site/media" public/storage
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
