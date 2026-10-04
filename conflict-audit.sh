#!/usr/bin/env bash
# Conflict audit: compare every file under updater/ with its main-app counterpart.
# Prints a bucketed inventory so feature-level differences can be reviewed
# before the update is applied.
set -u
cd "$(dirname "$0")"

only_updater=0; identical=0; differing=0; only_main=0
: > /tmp/conflict_only_updater.txt
: > /tmp/conflict_identical.txt
: > /tmp/conflict_differing.txt
: > /tmp/conflict_only_main.txt

while IFS= read -r up; do
  rel="${up#updater/}"
  main="$rel"
  if [ ! -e "$main" ]; then
    echo "ONLY_UPDATER $rel" >> /tmp/conflict_only_updater.txt
    only_updater=$((only_updater+1))
  elif cmp -s "$up" "$main"; then
    echo "IDENTICAL $rel" >> /tmp/conflict_identical.txt
    identical=$((identical+1))
  else
    added=$(diff "$main" "$up" | grep -c '^>')
    removed=$(diff "$main" "$up" | grep -c '^<')
    echo "DIFFERING $rel (+$added/-$removed updater-vs-main)" >> /tmp/conflict_differing.txt
    differing=$((differing+1))
  fi
done < <(find updater -type f -not -path '*/node_modules/*' | sort)

# Files present in main app areas that updater also manages, but absent from updater
while IFS= read -r main; do
  rel="${main#./}"
  case "$rel" in updater/*) continue;; esac
  if [ ! -e "updater/$rel" ]; then
    echo "ONLY_MAIN $rel" >> /tmp/conflict_only_main.txt
    only_main=$((only_main+1))
  fi
done < <(find ./app ./routes ./config ./resources/views ./database/migrations ./public/assets -type f \
           -not -path '*/node_modules/*' 2>/dev/null | sort)

echo "=== SUMMARY ==="
echo "identical   : $identical"
echo "differing   : $differing"
echo "only_updater: $only_updater"
echo "only_main   : $only_main"