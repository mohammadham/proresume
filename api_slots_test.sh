#!/bin/bash
# Focused test of the slot-based booking flow on /api/v1
BASE="http://localhost/proresume/api/v1"
SLOT_ID="${1:-100}"      # Wednesday 09:00-12:00
DATE="${2:-2026-10-07}" # a Wednesday

login() {
  curl -s -X POST -H "Accept: application/json" -H "Content-Type: application/json" \
    -d '{"login":"'"$1"'","password":"'"$2"'"}' "$BASE/login" \
    | grep -o '"token":"[^"]*"' | sed 's/.*"token":"//;s/"//'
}

echo "== slots for Wednesday $DATE =="
curl -s -H "Accept: application/json" "$BASE/appointments/slots/62?date=$DATE" \
  | tr ',' '\n' | grep -E '"(id|start|end|day|time|is_available|max_booking|booked_count)"' | head -30

T1=$(login romario@gmail.com 12345678)
echo
echo "== book slot $SLOT_ID on $DATE (user 62) =="
curl -s -X POST -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer $T1" \
  -d '{"provider_id":62,"booking_date":"'"$DATE"'","time_slot_id":'"$SLOT_ID"',"notes":"slot booking test"}' \
  "$BASE/appointments" | head -c 700; echo

echo
echo "== availability after booking =="
curl -s -H "Accept: application/json" "$BASE/appointments/slots/62?date=$DATE" \
  | tr '}' '}\n' | grep -E '"time"|is_available' | head -10

echo
echo "== a different user books the same slot again (2nd of max 3) =="
T2=$(login romario@gmail.com 12345678)
curl -s -X POST -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer $T2" \
  -d '{"provider_id":62,"booking_date":"'"$DATE"'","time_slot_id":'"$SLOT_ID"'}' \
  "$BASE/appointments" | head -c 300; echo

echo
echo "== wrong day for slot (Monday date, Wednesday slot) =="
curl -s -X POST -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer $T1" \
  -d '{"provider_id":62,"booking_date":"2026-10-05","time_slot_id":'"$SLOT_ID"'}' \
  "$BASE/appointments" | head -c 300; echo

echo
echo "== slot belonging to another provider =="
curl -s -X POST -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer $T1" \
  -d '{"provider_id":62,"booking_date":"'"$DATE"'","time_slot_id":99999}' \
  "$BASE/appointments" | head -c 300; echo

echo
echo "== my appointments =="
curl -s -H "Accept: application/json" -H "Authorization: Bearer $T1" "$BASE/appointments" \
  | head -c 700; echo