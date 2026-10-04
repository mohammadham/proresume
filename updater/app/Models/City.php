<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['province_id', 'name'];
    public $timestamps = true;

    public function province()
    {
        return $this->belongsTo(Province::class);
    }
}
