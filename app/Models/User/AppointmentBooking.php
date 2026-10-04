<?php

namespace App\Models\User;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppointmentBooking extends Model
{
    use HasFactory;

    protected $guarded;

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function provider()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
