# Bank Mellat (SADAD) Payment Gateway Implementation Plan

## API Specifications (Bank Mellat SADAD)

Based on research, Bank Mellat uses **SOAP/WSDL** API with the following endpoints:

### Production WSDL
- `https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl`

### Sandbox/Test WSDL
- `https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl` (same, with test credentials)

### Key SOAP Methods
1. **bpPayRequest** - Request payment token
2. **bpVerifyRequest** - Verify payment
3. **bpSettleRequest** - Settle/Refund payment
4. **bpInquiryRequest** - Inquiry transaction status
5. **bpReversalRequest** - Reverse transaction

### Required Parameters
| Parameter | Description | Source |
|-----------|-------------|--------|
| `terminalId` | Terminal ID (Merchant ID) | Gateway config |
| `userName` | Username | Gateway config |
| `userPassword` | Password | Gateway config |
| `orderId` | Unique order ID (numeric) | Generated |
| `amount` | Amount in Rials | Calculated |
| `localDate` | Current date (YYYYMMDD) | Generated |
| `localTime` | Current time (HHMMSS) | Generated |
| `additionalData` | Optional additional data | Optional |
| `callBackUrl` | Callback URL | Gateway config |

### Response Codes
| Code | Meaning |
|------|---------|
| 0 | Success |
| 11 | Invalid terminal |
| 12 | Invalid username/password |
| 13 | Invalid orderId |
| 14 | Invalid amount |
| 15 | Invalid date/time |
| 16 | Transaction not found |
| 17 | Already verified |
| 18 | Expired |
| 19 | Refund not allowed |
| 20 | Insufficient balance |
| 99 | General error |

### Payment Flow
1. Call `bpPayRequest` with order details → Get `refId` (token)
2. Redirect user to `https://bpm.shaparak.ir/pgwchannel/startpay.mellat?refId={refId}`
3. User pays on bank page
4. Bank redirects to `callBackUrl` with `RefId` and `ResCode`
5. Call `bpVerifyRequest` with `refId` → Confirm payment
6. Call `bpSettleRequest` to settle (or `bpReversalRequest` for refund)

---

## Implementation Plan

### Phase 1: Database & Configuration
1. Create seed migration for Bank Mellat gateway
2. Add configuration to `config/payment.php` (min amount, IP ranges)

### Phase 2: Controllers
3. Create `Payment/MellatController.php` (Admin flow)
4. Create `User/Payment/MellatController.php` (Vendor flow)
5. Implement SOAP client wrapper for Mellat API

### Phase 3: Routing & Middleware
6. Add routes in `routes/payment_gateways.php`
7. Add CSRF exceptions in `VerifyCsrfToken.php`
8. Apply rate limiting and IP whitelist middleware

### Phase 4: Admin/User Configuration
9. Add `mellatUpdate` method in `Admin/GatewayController.php`
10. Add `mellatUpdate` method in `User/GatewayController.php`
11. Update Blade views for admin/user gateway settings

### Phase 5: Checkout Integration
12. Update `Front/CheckoutController.php` with Mellat logic
13. Update `User/UserCheckoutController.php` with Mellat logic

### Phase 6: Testing & Documentation
14. Run migrations
15. Security testing (amount verification, idempotency, sandbox)
16. Create documentation

---

## Technical Architecture

### SOAP Client Implementation
```php
// Use PHP SoapClient with WSDL
$client = new SoapClient($wsdlUrl, [
    'trace' => 1,
    'exceptions' => true,
    'cache_wsdl' => WSDL_CACHE_NONE,
    'connection_timeout' => 30,
]);

// bpPayRequest
$result = $client->bpPayRequest([
    'terminalId' => $terminalId,
    'userName' => $username,
    'userPassword' => $password,
    'orderId' => $orderId,
    'amount' => $amount,
    'localDate' => $localDate,
    'localTime' => $localTime,
    'additionalData' => '',
    'callBackUrl' => $callbackUrl,
]);
// Result: bpPayRequestResult (string) - refId or error code
```

### Security Requirements
- Amount verification in verify step (P1-4)
- Idempotency with lockForUpdate (P1-5)
- Currency validation (IRR/IRT only)
- Minimum amount validation (10,000 Rial typical)
- Rate limiting (10 req/min)
- IP whitelisting (Shaparak IPs)
- Logging via payment channel
- CSRF exemption for callback URLs

---

## Configuration Fields

### Admin Gateway (payment_gateways table)
```json
{
    "terminal_id": "",
    "username": "",
    "password": "",
    "sandbox_status": 1,
    "callback_url": "",
    "description": "پرداخت اشتراک با بانک ملت"
}
```

### Vendor Gateway (user_payment_gateways table)
```json
{
    "terminal_id": "",
    "username": "",
    "password": "",
    "sandbox_status": 1,
    "callback_url": "",
    "text": "پرداخت امن با بانک ملت"
}
```

---

## Files to Create/Modify

### New Files
1. `updater/database/migrations/2026_08_29_000007_add_mellat_gateway.php`
2. `app/Http/Controllers/Payment/MellatController.php`
3. `app/Http/Controllers/User/Payment/MellatController.php`

### Modified Files
1. `routes/payment_gateways.php`
2. `app/Http/Middleware/VerifyCsrfToken.php`
3. `app/Http/Controllers/Admin/GatewayController.php`
4. `app/Http/Controllers/User/GatewayController.php`
4. `resources/views/admin/gateways/index.blade.php`
5. `resources/views/user/gateways/index.blade.php`
6. `app/Http/Controllers/Front/CheckoutController.php`
7. `app/Http/Controllers/User/UserCheckoutController.php`
8. `config/payment.php`

---

## Testing Checklist

### Sandbox Testing
- [ ] Configure with test terminal credentials
- [ ] Test successful payment flow
- [ ] Test cancelled payment (user cancels on bank page)
- [ ] Test amount mismatch detection
- [ ] Test duplicate callback (idempotency)
- [ ] Test verify with invalid refId
- [ ] Test refund/settle (if supported in sandbox)
- [ ] Verify Transaction records
- [ ] Check payment.log for all events

### Security Validation
- [ ] Amount verification works correctly
- [ ] Idempotency prevents double processing
- [ ] Rate limiting blocks excessive requests
- [ ] IP whitelist blocks unauthorized IPs (when enabled)
- [ ] CSRF exemption works for callbacks
- [ ] Proper error handling and logging
- [ ] Timeout handling (30 seconds)
- [ ] Currency validation rejects non-IRR/IRT

---

## Success Criteria
- All 5 existing Iranian gateways + Bank Mellat work consistently
- Same code patterns and security standards
- Full sandbox testing capability
- Production-ready with proper configuration
- Comprehensive documentation