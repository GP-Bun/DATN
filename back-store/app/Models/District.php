<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $fillable = [
        'code',
        'name',
        'slug',
        'type',
        'province_id',
    ];

    /**
     * Quận/huyện thuộc một tỉnh/thành phố
     */
    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Quận/huyện có nhiều phường/xã
     */
    public function wards()
    {
        return $this->hasMany(Ward::class);
    }
}
