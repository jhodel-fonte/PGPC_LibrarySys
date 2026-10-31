#!/usr/bin/env bash
set -euo pipefail

APP_DIR=/opt/pgpc-lib
if [[ $EUID -ne 0 ]]; then
  echo "Run this installer with sudo." >&2
  exit 1
fi
cd "$APP_DIR"
if [[ ! -f .env ]]; then
  echo "Missing $APP_DIR/.env. Configure it before installing the app." >&2
  exit 1
fi
if ! command -v npm >/dev/null 2>&1 || ! command -v node >/dev/null 2>&1; then
  echo "Install Node.js 20.19+ (or 22.12+) and npm before running this installer." >&2
  exit 1
fi
if ! node -e 'const [major, minor] = process.versions.node.split(".").map(Number); process.exit((major === 20 && minor >= 19) || (major >= 22 && (major > 22 || minor >= 12)) ? 0 : 1)'; then
  echo "Vite requires Node.js 20.19+ or 22.12+." >&2
  exit 1
fi
if ! grep -Eq '^APP_ENV=production$' .env || ! grep -Eq '^APP_DEBUG=false$' .env; then
  echo "Set APP_ENV=production and APP_DEBUG=false in .env first." >&2
  exit 1
fi

apt-get update
DEBIAN_FRONTEND=noninteractive apt-get install -y nginx composer php8.4-cli php8.4-fpm \
  php8.4-bcmath php8.4-curl php8.4-gd php8.4-intl php8.4-mbstring \
  php8.4-pgsql php8.4-xml php8.4-zip

php8.4 /usr/bin/composer install --no-interaction --no-dev --optimize-autoloader
npm ci
npm run build
if [[ ! -L public/storage ]]; then
  php8.4 artisan storage:link
fi
php8.4 artisan config:cache
php8.4 artisan view:cache

chown root:www-data "$APP_DIR"
chmod 750 "$APP_DIR"
chown -R www-data:www-data storage bootstrap/cache
chown root:www-data .env
chmod 640 .env
cat >/etc/php/8.4/fpm/conf.d/99-pgpc-lib.ini <<'PHPINI'
upload_max_filesize = 70M
post_max_size = 70M
max_execution_time = 300
PHPINI

install -m 644 deploy/pgpc-lib-nginx.conf /etc/nginx/sites-available/pgpc-lib
ln -sfn /etc/nginx/sites-available/pgpc-lib /etc/nginx/sites-enabled/pgpc-lib
install -m 644 deploy/pgpc-lib-queue.service /etc/systemd/system/pgpc-lib-queue.service
install -m 644 deploy/pgpc-lib-scheduler.service /etc/systemd/system/pgpc-lib-scheduler.service
install -m 644 deploy/pgpc-lib.target /etc/systemd/system/pgpc-lib.target
nginx -t
systemctl daemon-reload
systemctl enable --now pgpc-lib.target

echo "Laravel is available at http://library.pgpc.online. Start later with: sudo systemctl start pgpc-lib.target"
