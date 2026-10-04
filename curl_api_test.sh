#!/bin/bash
# End-to-end HTTP test: login as user 62, POST api-integration/update
BASE="http://localhost/proresume"
JAR="$(mktemp)"
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36"

login() {
  rm -f "$JAR"
  # get login page + csrf token
  local page
  page=$(curl -s -c "$JAR" -A "$UA" "$BASE/user/login")
  local token
  token=$(echo "$page" | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//')
  echo "csrf: ${token:0:12}..."
  # post login
  curl -s -b "$JAR" -c "$JAR" -A "$UA" -o /dev/null -w "login: %{http_code} -> %{redirect_url}\n" \
    -d "_token=$token" -d "email=romario@gmail.com" -d "password=12345678" \
    "$BASE/login"
}

post_form() {
  local path="$1"; shift
  local data=("$@")
  local page token
  page=$(curl -s -b "$JAR" -c "$JAR" -A "$UA" "$BASE$path")
  token=$(echo "$page" | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//')
  local args=(-s -b "$JAR" -c "$JAR" -A "$UA" -o /dev/null -w "%{http_code} %{redirect_url}" -d "_token=$token")
  for d in "${data[@]}"; do args+=(-d "$d"); done
  curl "${args[@]}" "$BASE$path"
}

echo "== login =="
login
echo "== check auth =="
curl -s -b "$JAR" -c "$JAR" -A "$UA" "$BASE/user/api-integration" | grep -o '<title>[^<]*</title>'
echo "== POST api-integration/update =="
post_form "/user/api-integration/update" "app_type=doctor" "is_active=1"
echo ""
rm -f "$JAR"