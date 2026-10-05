<?php

namespace App\Console\Commands;

use App\Models\BasicSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Re-verifies the site's Enamad trust seal against api.enamad.ir.
 *
 * config/enamad.php promises a 24h verification interval
 * (verification.verify_interval_hours) but nothing ever implemented it - the
 * only way the stored status ever changed was the manual button in the admin
 * panel. This command performs that check server-side, updates
 * enamad_status / enamad_expire_date, and clears the status cache.
 */
class VerifyEnamad extends Command
{
    protected $signature = 'enamad:verify';

    protected $description = 'Re-verify the Enamad trust seal status with api.enamad.ir';

    public function handle()
    {
        $bs = BasicSetting::first();

        if (!$bs || !$bs->enamad_site_id || !$bs->enamad_secret_key) {
            $this->info('Enamad credentials are not configured; nothing to verify.');
            return 0;
        }

        $config = config('enamad');

        try {
            $response = Http::timeout($config['api']['timeout'])
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($config['api']['base_url'] . $config['api']['verify_endpoint'], [
                    'site_id' => $bs->enamad_site_id,
                    'secret_key' => $bs->enamad_secret_key,
                    'domain' => parse_url(config('app.url'), PHP_URL_HOST) ?: request()->getHost(),
                ]);

            if (!$response->successful()) {
                $error = $response->json('message') ?? "HTTP {$response->status()}";

                Log::channel('enamad')->error('Scheduled Enamad verification failed', [
                    'status_code' => $response->status(),
                    'error' => $error,
                ]);
                $this->error("Verification failed: {$error}");
                return 1;
            }

            $result = $response->json();

            // Malformed success payloads (empty body, an HTML error page sent
            // with HTTP 200, JSON without a 'status' field) used to fall
            // through to status=unknown and silently write enamad_status=0
            // with exit code 0, indistinguishable from a real 'unknown'
            // verdict. A scheduled verifier must fail loudly instead: log it,
            // report it and leave the stored row untouched.
            if (!is_array($result) || !array_key_exists('status', $result)) {
                Log::channel('enamad')->error('Scheduled Enamad verification returned a malformed payload', [
                    'http_status' => $response->status(),
                    'body' => substr($response->body(), 0, 500),
                ]);
                $this->error('Verification failed: malformed response payload (no status field)');
                return 1;
            }

            $status = $result['status'];
            $isValid = in_array($status, ['active', 'verified'], true);

            $updateData = ['enamad_status' => $isValid ? 1 : 0];
            if (!empty($result['expire_date'])) {
                $updateData['enamad_expire_date'] = $result['expire_date'];
            }
            $bs->update($updateData);

            Cache::forget($config['cache']['prefix'] . 'status');
            Cache::forget('enamad_status');

            Log::channel('enamad')->info('Scheduled Enamad verification completed', [
                'status' => $status,
                'expire_date' => $result['expire_date'] ?? null,
            ]);

            $this->info("Enamad verification completed: status={$status}, valid=" . ($isValid ? 'yes' : 'no'));
            return 0;
        } catch (\Exception $e) {
            Log::channel('enamad')->error('Scheduled Enamad verification exception', [
                'error' => $e->getMessage(),
            ]);
            $this->error('Verification error: ' . $e->getMessage());
            return 1;
        }
    }
}
