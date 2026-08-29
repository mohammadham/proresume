<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ApiIntegration;
use App\Models\User;
use App\Models\Province;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiIntegrationController extends Controller
{
    public function index()
    {
        $user = Auth::guard('web')->user();
        $data = $user->apiIntegration;
        $provinces = Province::all();
        $cities = collect();

        if ($user->state) {
            $province = Province::where('name', $user->state)->first();
            if ($province) {
                $cities = City::where('province_id', $province->id)->get();
            }
        }

        return view('user.settings.api-integration', compact('data', 'provinces', 'cities'));
    }

    public function update(Request $request)
    {
        $user = Auth::guard('web')->user();

        $request->validate([
            'app_type' => 'required|in:barber,doctor',
            'is_active' => 'nullable|boolean',
        ]);

        $integration = ApiIntegration::where('user_id', $user->id)->first();

        if (!$integration) {
            $integration = new ApiIntegration();
            $integration->user_id = $user->id;
            $integration->api_key = ApiIntegration::generateApiKey();
        }

        $integration->app_type = $request->app_type;
        $integration->is_active = $request->has('is_active');
        $integration->save();

        return redirect()->back()->with('success', 'تنظیمات با موفقیت ذخیره شد');
    }

    public function regenerateKey(Request $request)
    {
        $user = Auth::guard('web')->user();
        $integration = ApiIntegration::where('user_id', $user->id)->first();

        if ($integration) {
            $integration->api_key = ApiIntegration::generateApiKey();
            $integration->save();
        }

        return redirect()->back()->with('success', 'API Key با موفقیت بازنشانی شد');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::guard('web')->user();

        $request->validate([
            'service_type' => 'nullable|in:barber,doctor',
            'specialty' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
        ]);

        $user->update([
            'service_type' => $request->service_type,
            'specialty' => $request->specialty,
            'state' => $request->state,
            'city' => $request->city,
            'district' => $request->district,
            'address' => $request->address,
            'lat' => $request->lat,
            'lng' => $request->lng,
        ]);

        return redirect()->back()->with('success', 'پروفایل با موفقیت به‌روزرسانی شد');
    }

    public function getCities(Request $request, $provinceId)
    {
        $cities = City::where('province_id', $provinceId)->get(['id', 'name']);
        return response()->json($cities);
    }
}
