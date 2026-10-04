#!/bin/bash
# End-to-end test of the /api/v1 surface (Sanctum token auth)
BASE="http://localhost/proresume/api/v1"
J="api_test_out.json"
hdr() { echo; echo "──────── $* ────────"; }
req() { # method path [data] [token]
  local m="$1" p="$2" d="$3" t="$4"
  local args=(-s -X "$m" -H "Accept: application/json" -H "Content-Type: application/json")
  [ -n "$t" ] && args+=(-H "Authorization: Bearer $t")
  [ -n "$d" ] && args+=(-d "$d")
  curl "${args[@]}" "$BASE$p"
}

hdr "1. GET /providers (no filter)"
req GET "/providers" "" "" | head -c 600; echo

hdr "2. GET /providers?service_type=doctor"
req GET "/providers?service_type=doctor" "" "" | head -c 600; echo

hdr "3. GET /providers?service_type=barber"
req GET "/providers?service_type=barber" "" "" | head -c 400; echo

hdr "4. GET /providers/map"
req GET "/providers/map" "" "" | head -c 600; echo

hdr "5. GET /providers/62"
req GET "/providers/62" "" "" | head -c 600; echo

hdr "6. GET /providers/999999 (expect 404)"
req GET "/providers/999999" "" "" | head -c 300; echo

hdr "7. POST /login (wrong password -> 422)"
req POST "/login" '{"login":"romario@gmail.com","password":"WRONG"}' "" | head -c 300; echo

hdr "8. POST /login (correct)"
LOGIN=$(req POST "/login" '{"login":"romario@gmail.com","password":"12345678"}' "")
echo "$LOGIN" | head -c 400; echo
TOKEN=$(echo "$LOGIN" | grep -o '"token":"[^"]*"' | sed 's/.*"token":"//;s/"//')
echo "token: ${TOKEN:0:20}..."

hdr "9. GET /profile (with token)"
req GET "/profile" "" "$TOKEN" | head -c 500; echo

hdr "10. GET /profile (no token -> 401)"
req GET "/profile" "" "" | head -c 300; echo

hdr "11. POST /register (new user)"
EMAIL="apitest$(date +%s)@example.com"
REG=$(req POST "/register" "{\"name\":\"API Test User\",\"email\":\"$EMAIL\",\"password\":\"secret123\",\"password_confirmation\":\"secret123\",\"phone\":\"09120000000\"}")
echo "$REG" | head -c 500; echo

hdr "12. POST /register (duplicate email -> 422)"
req POST "/register" "{\"name\":\"Dup\",\"email\":\"$EMAIL\",\"password\":\"secret123\",\"password_confirmation\":\"secret123\"}" "" | head -c 300; echo

hdr "13. POST /register (validation errors -> 422)"
req POST "/register" '{"name":"","email":"not-an-email","password":"12"}' "" | head -c 300; echo

hdr "14. GET /appointments/slots/62 (no date -> 422)"
req GET "/appointments/slots/62" "" "$TOKEN" | head -c 400; echo

hdr "15. GET /appointments/slots/62?date=2026-10-07"
req GET "/appointments/slots/62?date=2026-10-07" "" "$TOKEN" | head -c 800; echo

hdr "16. GET /appointments (with token)"
req GET "/appointments" "" "$TOKEN" | head -c 500; echo

hdr "17. POST /appointments (booking)"
req POST "/appointments" '{"provider_id":62,"booking_date":"2026-10-07","notes":"API test booking"}' "$TOKEN" | head -c 600; echo

hdr "18. POST /appointments (invalid provider -> 422)"
req POST "/appointments" '{"provider_id":999999,"booking_date":"2026-10-07"}' "$TOKEN" | head -c 300; echo

hdr "19. POST /appointments (no auth -> 401)"
req POST "/appointments" '{"provider_id":62,"booking_date":"2026-10-07"}' "" | head -c 300; echo

hdr "20. POST /logout"
req POST "/logout" "" "$TOKEN" | head -c 300; echo

hdr "21. GET /profile after logout (expect 401)"
req GET "/profile" "" "$TOKEN" | head -c 300; echo

echo; echo "done."