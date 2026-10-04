<?php

namespace App\Http\Controllers;

use App\Models\BasicSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EnamadController extends Controller
{
    private $config;

    public function __construct()
    {
        $this->config = config('enamad');
    }

    /**
     * Verify Enamad status with Enamad API
     */
    public function verify(Request $request)
    {
        try {
            $bs = BasicSetting::first();
            
            if (!$bs || !$bs->enamad_site_id || !$bs->enamad_secret_key) {
                return response()->json([
                    'success' => false,
                    'message' => 'اطلاعات ایناماد تنظیم نشده است.'
                ], 400);
            }

            $response = Http::timeout($this->config['api']['timeout'])
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($this->config['api']['base_url'] . $this->config['api']['verify_endpoint'], [
                    'site_id' => $bs->enamad_site_id,
                    'secret_key' => $bs->enamad_secret_key,
                    'domain' => request()->getHost(),
                ]);

            if ($response->successful()) {
                $result = $response->json();
                
                $status = $result['status'] ?? 'unknown';
                $expireDate = $result['expire_date'] ?? null;
                
                // Update basic settings
                $updateData = ['enamad_status' => ($status === 'active' || $status === 'verified') ? 1 : 0];
                
                if ($expireDate) {
                    $updateData['enamad_expire_date'] = $expireDate;
                }
                
                BasicSetting::first()->update($updateData);
                
                // Clear cache
                Cache::forget('enamad_status');
                Cache::forget('enamad_verify');
                
                Log::channel('enamad')->info('Enamad verification completed', [
                    'status' => $status,
                    'expire_date' => $expireDate,
                    'domain' => request()->getHost(),
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'تایید اعتبار ایناماد با موفقیت انجام شد.',
                    'data' => [
                        'status' => $status,
                        'expire_date' => $expireDate,
                        'is_valid' => ($status === 'active' || $status === 'verified'),
                    ]
                ]);
            } else {
                $error = $response->json()['message'] ?? 'خطا در ارتباط با سرور ایناماد';
                
                Log::channel('enamad')->error('Enamad verification failed', [
                    'status_code' => $response->status(),
                    'error' => $error,
                    'domain' => request()->getHost(),
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => $error
                ], 400);
            }
            
        } catch (\Exception $e) {
            Log::channel('enamad')->error('Enamad verification exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'خطا در ارتباط با سرور ایناماد: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Enamad status
     */
    public function status()
    {
        $cacheKey = 'enamad_status';
        
        return Cache::remember($this->config['cache']['prefix'] . 'status', 
            $this->config['cache']['status_ttl'], function () {
            $bs = BasicSetting::first();
            
            return [
                'enamad_status' => $bs->enamad_status ?? 0,
                'enamad_code' => $bs->enamad_code ?? '',
                'enamad_site_id' => $bs->enamad_site_id ?? '',
                'enamad_expire_date' => $bs->enamad_expire_date ?? null,
                'enamad_logo_type' => $bs->enamad_logo_type ?? 'auto',
                'is_valid' => $this->checkValidity(),
            ];
        });
    }

    /**
     * Get Enamad Trust Seal logo URL
     */
    public function logo()
    {
        $bs = BasicSetting::first();
        
        if (!$bs->enamad_site_id) {
            return response()->json(['error' => 'Site ID not configured'], 400);
        }
        
        $logoType = $bs->enamad_logo_type ?? 'auto';
        $logoUrl = config('enamad.trust_seal.base_url') . config('enamad.trust_seal.logo_endpoint') 
            . '?id=' . $bs->enamad_site_id 
            . '&type=' . $logoType;
        
        return response()->json([
            'logo_url' => $logoUrl,
            'site_id' => $bs->enamad_site_id,
            'type' => $logoType,
        ]);
    }

    /**
     * Verify Enamad status manually (AJAX call from admin panel)
     */
    public function manualVerify(Request $request)
    {
        return $this->verify($request);
    }

    /**
     * Check if Enamad is valid (not expired)
     */
    private function checkValidity(): bool
    {
        $bs = BasicSetting::first();
        
        if (!$bs->enamad_status || !$bs->enamad_site_id) {
            return false;
        }
        
        if ($bs->enamad_expire_date) {
            return \Carbon\Carbon::parse($bs->enamad_expire_date)->isFuture();
        }
        
        return true;
    }
}