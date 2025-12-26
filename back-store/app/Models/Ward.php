<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    protected $fillable = [
        'code',
        'name',
        'slug',
        'type',
        'district_id',
    ];

    /**
     * Phường/xã thuộc một quận/huyện
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
