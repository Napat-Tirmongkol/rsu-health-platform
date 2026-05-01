#!/usr/bin/env bash
# ══════════════════════════════════════════════════════════════════
#  RSU Health Platform — Plesk Production Deploy Script
#  รัน script นี้บน server ผ่าน SSH Terminal ใน Plesk
#  ก่อนรัน: แก้ DEPLOY_PATH และ PHP_BIN ด้านล่างให้ถูกต้อง
# ══════════════════════════════════════════════════════════════════

set -euo pipefail

# ── CONFIG (แก้ค่าเหล่านี้ให้ตรงกับ server) ──────────────────────
DEPLOY_PATH="/var/www/vhosts/YOUR_DOMAIN.com/httpdocs"
PHP_BIN="/usr/bin/php8.2"           # หรือ /opt/plesk/php/8.2/bin/php
COMPOSER_BIN="/usr/local/bin/composer"

# ─────────────────────────────────────────────────────────────────
ARTISAN="$PHP_BIN $DEPLOY_PATH/artisan"
cd "$DEPLOY_PATH"

echo ""
echo "╔══════════════════════════════════════════╗"
echo "║  RSU Health Platform — Production Deploy ║"
echo "╚══════════════════════════════════════════╝"
echo ""

# ── 1. Maintenance mode ON ────────────────────────────────────────
echo "→ [1/9] Enabling maintenance mode..."
$ARTISAN down --retry=10

# ── 2. Pull latest code ───────────────────────────────────────────
echo "→ [2/9] Pulling latest code from Git..."
git pull origin main

# ── 3. Composer dependencies ──────────────────────────────────────
echo "→ [3/9] Installing Composer dependencies (production)..."
$PHP_BIN $COMPOSER_BIN install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# ── 4. Build frontend assets ──────────────────────────────────────
echo "→ [4/9] Building frontend assets (Vite)..."
if command -v npm &>/dev/null; then
    npm ci --omit=dev
    npm run build
else
    echo "   ⚠  npm not found — upload pre-built public/build/ manually"
fi

# ── 5. Run migrations ─────────────────────────────────────────────
echo "→ [5/9] Running database migrations..."
$ARTISAN migrate --force

# ── 6. Clear + rebuild caches ────────────────────────────────────
echo "→ [6/9] Optimising application..."
$ARTISAN optimize:clear
$ARTISAN config:cache
$ARTISAN route:cache
$ARTISAN view:cache
$ARTISAN event:cache

# ── 7. Storage permissions ───────────────────────────────────────
echo "→ [7/9] Setting storage permissions..."
chmod -R 775 storage bootstrap/cache
chown -R "$(whoami)":psaserv storage bootstrap/cache 2>/dev/null || true

# ── 8. Restart queue worker ──────────────────────────────────────
echo "→ [8/9] Restarting queue worker..."
$ARTISAN queue:restart

# ── 9. Maintenance mode OFF ──────────────────────────────────────
echo "→ [9/9] Disabling maintenance mode..."
$ARTISAN up

echo ""
echo "✓ Deploy complete — $(date '+%Y-%m-%d %H:%M:%S')"
echo ""
