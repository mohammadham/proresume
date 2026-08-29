<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Customer;
use App\Models\User\UserTimeSlot;
use App\Models\User\AppointmentBooking;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function slots(Request $request, $providerId)
    {
        $provider = User::where('id', $providerId)
            ->where('status', 1)
            ->whereNotNull('service_type')
            ->firstOrFail();

        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $date = $request->date;
        $dayOfWeek = \Carbon\Carbon::parse($date)->format('l');

        $slots = UserTimeSlot::where('user_id', $providerId)
            ->where('day', $dayOfWeek)
            ->where('is_active', 1)
            ->orderBy('start_time')
            ->get();

        $bookedSlots = AppointmentBooking::where('user_id', $providerId)
            ->where('booking_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('time_slot_id')
            ->toArray();

        $slots->each(function ($slot) use ($bookedSlots) {
            $slot->is_available = !in_array($slot->id, $bookedSlots);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'provider' => $provider,
                'date' => $date,
                'slots' => $slots
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'provider_id' => 'required|exists:users,id',
            'category_id' => 'nullable|exists:categories,id',
            'booking_date' => 'required|date_format:Y-m-d',
            'time_slot_id' => 'nullable|exists:user_time_slots,id',
            'notes' => 'nullable|string',
        ]);

        $provider = User::where('id', $request->provider_id)
            ->where('status', 1)
            ->whereNotNull('service_type')
            ->firstOrFail();

        if ($request->time_slot_id) {
            $existingBooking = AppointmentBooking::where('user_id', $request->provider_id)
                ->where('booking_date', $request->booking_date)
                ->where('time_slot_id', $request->time_slot_id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->first();

            if ($existingBooking) {
                return response()->json([
                    'success' => false,
                    'message' => 'این بازه زمانی قبلاً رزرو شده است'
                ], 409);
            }
        }

        $price = null;
        if ($request->category_id) {
            $category = \App\Models\User\Category::where('id', $request->category_id)
                ->where('user_id', $request->provider_id)
                ->first();
            if ($category) {
                $price = $category->appointment_price;
            }
        }

        $customer = Customer::where('user_id', $request->user()->id)->first();
        if (!$customer) {
            $user = $request->user();
            $nameParts = explode(' ', $user->name, 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            $customer = Customer::create([
                'user_id' => $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $user->email,
                'password' => $user->password,
                'contact_number' => $user->phone,
                'status' => 1,
            ]);
        }

        $appointment = AppointmentBooking::create([
            'user_id' => $request->provider_id,
            'customer_id' => $customer->id,
            'category_id' => $request->category_id,
            'booking_date' => $request->booking_date,
            'time_slot_id' => $request->time_slot_id,
            'price' => $price,
            'status' => 'pending',
            'payment_status' => 0,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'نوبت شما با موفقیت ثبت شد',
            'data' => $appointment
        ], 201);
    }

    public function myAppointments(Request $request)
    {
        $user = $request->user();
        $customer = Customer::where('user_id', $user->id)->first();

        if (!$customer) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $appointments = AppointmentBooking::where('customer_id', $customer->id)
            ->with(['provider', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }
}
