<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Models\User\AppointmentBooking;
use App\Models\User\BasicSetting;
use App\Models\User\Category;
use App\Models\User\UserTimeSlot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    /** Statuses that still occupy a slot (4 = cancelled). */
    const ACTIVE_STATUSES = [1, 2, 3];

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
        $day = Carbon::parse($date)->format('l');

        $slots = UserTimeSlot::where('user_id', $providerId)
            ->whereRaw('LOWER(day) = ?', [strtolower($day)])
            ->orderBy('start')
            ->get();

        // how many active bookings exist per "start - end" label for that date
        $booked = AppointmentBooking::where('user_id', $providerId)
            ->where('date', $date)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->get(['time'])
            ->groupBy('time')
            ->map(fn ($rows) => $rows->count())
            ->all();

        $slots->each(function ($slot) use ($booked) {
            $label = $slot->start . ' - ' . $slot->end;
            $taken = $booked[$label] ?? 0;

            $slot->time = $label;
            $slot->booked_count = $taken;
            $slot->is_available = $slot->max_booking ? ($taken < $slot->max_booking) : ($taken === 0);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'provider' => $provider,
                'date' => $date,
                'day' => $day,
                'slots' => $slots,
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

        $user = $request->user();
        $basicSetting = BasicSetting::where('user_id', $provider->id)->first();
        $customer = Customer::where('user_id', $user->id)->first();

        $slot = null;
        if ($request->time_slot_id) {
            $slot = UserTimeSlot::where('id', $request->time_slot_id)
                ->where('user_id', $provider->id)
                ->first();

            if (!$slot) {
                return response()->json([
                    'success' => false,
                    'message' => 'بازه زمانی انتخاب شده متعلق به این ارائه‌دهنده نیست',
                ], 422);
            }
        }

        $slotLabel = $slot ? $slot->start . ' - ' . $slot->end : null;

        if ($slotLabel) {
            $day = Carbon::parse($request->booking_date)->format('l');
            if (strtolower($slot->day) !== strtolower($day)) {
                return response()->json([
                    'success' => false,
                    'message' => 'بازه زمانی برای این روز هفته معتبر نیست',
                ], 422);
            }

            $taken = AppointmentBooking::where('user_id', $provider->id)
                ->where('date', $request->booking_date)
                ->where('time', $slotLabel)
                ->whereIn('status', self::ACTIVE_STATUSES)
                ->count();

            if ($slot->max_booking ? $taken >= $slot->max_booking : $taken > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'این بازه زمانی قبلاً رزرو شده است',
                ], 409);
            }
        }

        $price = $basicSetting->appointment_price ?? 0;
        if ($request->category_id) {
            $category = Category::where('id', $request->category_id)
                ->where('user_id', $provider->id)
                ->first();
            if ($category) {
                $price = $category->appointment_price;
            }
        }

        if (!$customer) {
            $nameParts = explode(' ', trim($user->first_name . ' ' . $user->last_name), 2);

            $customer = Customer::create([
                'user_id' => $user->id,
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1] ?? '',
                'email' => $user->email,
                'password' => $user->password,
                'contact_number' => $user->phone,
                'status' => 1,
            ]);
        }

        $sl = BasicSetting::select(DB::raw("serial_reset, CONCAT('', LPAD(serial_reset + 1, 5, '0')) as slId"))
            ->where('user_id', $provider->id)
            ->first();
        $serialNumber = $sl ? $sl->slId : '00001';

        $appointment = AppointmentBooking::create([
            'customer_id' => $customer->id,
            'user_id' => $provider->id,
            'serial_number' => $serialNumber,
            'name' => trim($user->first_name . ' ' . $user->last_name) ?: $user->email,
            'email' => $user->email,
            'date' => $request->booking_date,
            'time' => $slotLabel,
            'category_id' => $request->category_id,
            'amount' => 0,
            'total_amount' => $price,
            'due_amount' => $price,
            'details' => $request->notes,
            'currency' => $basicSetting->currency ?? null,
            'payment_method' => 'api',
            'status' => 1,
            'payment_status' => 1,
        ]);

        if ($sl) {
            $basicSetting->serial_reset = $sl->serial_reset + 1;
            $basicSetting->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'نوبت شما با موفقیت ثبت شد',
            'data' => $appointment,
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
            ->orderBy('id', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }
}