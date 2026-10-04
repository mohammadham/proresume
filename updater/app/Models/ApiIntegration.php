<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApiIntegration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'api_key',
        'is_active',
        'app_type',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function generateApiKey()
    {
        return hash('sha256', \Illuminate\Support\Str::random(60));
    }
}
