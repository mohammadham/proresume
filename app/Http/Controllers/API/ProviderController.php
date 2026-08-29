<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->where('status', 1)
            ->whereNotNull('service_type')
            ->with(['apiIntegration']);

        if ($request->has('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        if ($request->has('province')) {
            $query->where('state', $request->province);
        }

        if ($request->has('city')) {
            $query->where('city', $request->city);
        }

        if ($request->has('district')) {
            $query->where('district', $request->district);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $query->whereHas('apiIntegration', function ($q) {
            $q->where('is_active', true);
        });

        $providers = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $providers
        ]);
    }

    public function show($id)
    {
        $provider = User::where('id', $id)
            ->where('status', 1)
            ->whereNotNull('service_type')
            ->with(['apiIntegration'])
            ->firstOrFail();

        if (!$provider->apiIntegration || !$provider->apiIntegration->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'ارائه‌دهنده یافت نشد'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $provider
        ]);
    }

    public function map(Request $request)
    {
        $query = User::query()
            ->where('status', 1)
            ->whereNotNull('service_type')
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->with(['apiIntegration']);

        if ($request->has('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        if ($request->has('province')) {
            $query->where('state', $request->province);
        }

        if ($request->has('city')) {
            $query->where('city', $request->city);
        }

        $query->whereHas('apiIntegration', function ($q) {
            $q->where('is_active', true);
        });

        $providers = $query->get(['id', 'first_name', 'last_name', 'lat', 'lng', 'city', 'address', 'photo']);

        return response()->json([
            'success' => true,
            'data' => $providers
        ]);
    }
}
