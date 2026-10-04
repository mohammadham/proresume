<?php

namespace App\Services;

use App\Models\User;
use App\Models\City;
use App\Models\Province;

class LocationService
{
    public function getProvidersWithinRadius(float $lat, float $lng, float $radiusKm, array $filters = [])
    {
        $haversine = "(6371 * acos(cos(radians($lat))
                    * cos(radians(users.lat))
                    * cos(radians(users.lng) - radians($lng))
                    * sin(radians($lat))))";

        $query = User::query()
            ->select('users.*')
            ->selectRaw("$haversine as distance")
            ->where('status', 1)
            ->whereNotNull('service_type')
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->having('distance', '<=', $radiusKm)
            ->orderBy('distance');

        if (isset($filters['service_type'])) {
            $query->where('service_type', $filters['service_type']);
        }

        if (isset($filters['province'])) {
            $query->where('state', $filters['province']);
        }

        if (isset($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $query->whereHas('apiIntegration', function ($q) {
            $q->where('is_active', true);
        });

        return $query->get();
    }

    public function getProvinceIdByName(string $name): ?int
    {
        return Province::where('name', $name)->value('id');
    }

    public function getCitiesByProvince(string $provinceName)
    {
        $province = Province::where('name', $provinceName)->first();
        if (!$province) {
            return collect();
        }
        return City::where('province_id', $province->id)->get();
    }

    public function geocode(string $address): ?array
    {
        return null;
    }
}
