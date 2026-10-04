#!/usr/bin/env bash
# Additive application of the updater/ payload.
#
# updater/UpdateController.php applies the update with action="replace" on
# app/, config/, database/migrations/, resources/views/ and routes/ - it deletes
# those directories and re-copies the payload. That would destroy everything
# added on the main install (Enamad, watermark, the /api/v1 Sanctum API, the
# Iranian gateway routes and their 1,100-line admin/user UI, Sanctum provider
# registration, ...). So only the payload's genuinely new content is taken:
#   * assets that do not exist in the main install yet
#   * migrations the main install does not have yet
# and everything that differs is left alone (see conflict-audit output).
set -euo pipefail
cd "$(dirname "$0")"

copied_assets=0
copied_migrations=0

# --- 1. New migrations -------------------------------------------------------
# 2026_08_24_000006..000010 are duplicates of 2026_01_15_000002..000006
# (api_integrations, api fields, service fields, provinces, cities) which the main
# install already applied, so they are deliberately skipped.
for m in \
  2026_08_24_000001_add_zarinpal_gateway \
  2026_08_24_000002_add_zibal_gateway \
  2026_08_24_000003_add_idpay_gateway \
  2026_08_24_000004_add_nextpay_gateway \
  2026_08_24_000005_add_payir_gateway \
  2026_08_30_000001_add_mellat_gateway \
  2026_08_30_000002_add_mellat_sale_fields_to_transactions \
  2026_08_30_000003_add_watermark_to_basic_settings \
  2026_08_30_000004_add_watermark_to_user_basic_settings \
  2026_08_30_000005_add_enamad_to_user_basic_settings
do
  src="updater/database/migrations/$m.php"
  [ -f "$src" ] || { echo "MISSING  $src"; continue; }
  if [ -f "database/migrations/$m.php" ]; then
    echo "skip     database/migrations/$m.php (already present)"
  else
    cp "$src" "database/migrations/$m.php"
    echo "added    database/migrations/$m.php"
    copied_migrations=$((copied_migrations+1))
  fi
done

# --- 2. New assets -----------------------------------------------------------
# The payload keeps assets/ at its root (in a real deployment the updater lives
# under public/, and UpdateController maps public/updater/assets -> public/assets).
while IFS= read -r src; do
  rel="${src#updater/}"
  dest="public/$rel"
  if [ ! -e "$dest" ]; then
    mkdir -p "$(dirname "$dest")"
    cp "$src" "$dest"
    copied_assets=$((copied_assets+1))
  fi
done < <(find updater/assets -type f 2>/dev/null | sort)

echo
echo "migrations added : $copied_migrations"
echo "assets added     : $copied_assets"
echo
echo "SKIPPED (would have destroyed main-install features):"
echo "  app/            - EnamadController, API/*, ApiIntegrationController, Mellat/gateways"
echo "  config/         - sanctum.php, enamad.php, logging channels, Sanctum provider"
echo "  resources/views - admin|user enamad, watermark, api-integration, gateways UI"
echo "  routes/         - payment_gateways.php, /api/v1 group, enamad|watermark|api routes"
echo "  composer.json   - laravel/sanctum dependency"