#!/bin/bash
# Save all 6 Iranian gateway configs through the admin panel exactly as the UI form does.
BASE="http://localhost/proresume"
JAR=/tmp/gwsave.txt
rm -f $JAR
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0 Safari/537.36"

tok() { curl -s -b $JAR -c $JAR -A "$UA" "$BASE/admin/gateways" | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//'; }

page=$(curl -s -c $JAR -A "$UA" "$BASE/admin")
t=$(echo "$page" | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//')
curl -s -b $JAR -c $JAR -A "$UA" -o /dev/null -e "$BASE/admin" \
  -d "_token=$t" -d "username=admin" -d "password=admin" "$BASE/admin/login"
echo "logged in: $(curl -s -b $JAR -c $JAR -A "$UA" -e "$BASE/admin" $BASE/admin/dashboard | grep -o '<title>[^<]*' | head -1)"

save() {
  local url="$1"; shift
  local t; t=$(tok)
  local args=(-s -b $JAR -c $JAR -A "$UA" -e "$BASE/admin/gateways" -o /dev/null
              -w "%{http_code}" -d "_token=$t")
  for d in "$@"; do args+=(-d "$d"); done
  printf "%-12s -> HTTP %s\n" "$url" "$(curl "${args[@]}" "$BASE$url")"
}

save /zarinpal  "status=1" "merchant_id=00000000-1111-2222-3333-444444444444" "sandbox_status=1" "callback_url=$BASE/zarinpal/success"
save /zibal    "status=1" "merchant_id=zibal-test-1234"  "sandbox_status=1" "description=پرداخت اشتراک تست"
save /idpay    "status=1" "api_key=idpay-test-key-abc"   "sandbox_status=1"
save /nextpay  "status=1" "api_key=nextpay-test-key-abc" "sandbox_status=1"
save /payir    "status=1" "api_key=payir-test-key-abc"   "sandbox_status=1"
save /mellat   "status=1" "terminal_id=TESTTERM1234" "username=testuser" "password=testpass" "sandbox_status=1" "callback_url=$BASE/mellat/success"