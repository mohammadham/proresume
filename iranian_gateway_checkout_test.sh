#!/bin/bash
# Exercise each Iranian gateway through the real membership checkout flow.
# We only inspect the redirect each gateway controller produces (we cannot
# complete a payment without real bank credentials).
BASE="http://localhost/proresume"
JAR=/tmp/checkout.txt
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0 Safari/537.36"

PKG=$(php artisan tinker --execute="echo \App\Models\Package::where('price','>',0)->value('id');" 2>/dev/null | grep -Eo '^[0-9]+$' | head -1)
PRICE=$(php artisan tinker --execute="echo \App\Models\Package::find($PKG)->price;" 2>/dev/null | grep -Eo '^[0-9.]+$' | head -1)
echo "package_id=$PKG price=$PRICE"

for GW in ZarinPal Zibal IdPay NextPay Pay.ir Mellat; do
  rm -f $JAR
  page=$(curl -s -c $JAR -A "$UA" "$BASE/pricing")
  tok=$(echo "$page" | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//')
  out=$(curl -s -b $JAR -c $JAR -A "$UA" -e "$BASE/pricing" -o /dev/null -w "%{http_code} %{redirect_url}" \
    -d "_token=$tok" \
    -d "first_name=Test" -d "last_name=User" -d "username=gwtest$GW" \
    -d "password=Test@12345" -d "email=gwtest$RANDOM@example.com" -d "phone=09120000000" \
    -d "city=Tehran" -d "country=Iran" -d "package_id=$PKG" -d "price=$PRICE" \
    -d "payment_method=$GW" -d "is_receipt=0" \
    "$BASE/membership/checkout")
  printf "%-10s -> %s\n" "$GW" "$(echo "$out" | cut -c1-150)"
done