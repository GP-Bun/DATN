<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Address - Địa chỉ giao hàng
 */
class Address extends Model
{
    use HasFactory;

    protected $appends = ['full_address'];

    protected $fillable = [
        'user_id',
        'receiver_name',
        'receiver_phone',
        'line1',
        'zip',
        'is_default',
        'is_saved',
        'province_id',
        'district_id',
        'ward_id',
        'province_code',
        'district_code',
        'ward_code'
    ];


    // Quan hệ với User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Quan hệ địa lý
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    // Scope địa chỉ mặc định
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function isDefault(): bool
    {
        return (bool) $this->is_default;
    }

    // Hàm trả về địa chỉ đầy đủ dạng text
    public function getFullAddressAttribute(): string
    {
        $parts = [];
        if ($this->line1) {
            $parts[] = $this->line1;
        }
        if ($this->ward) {
            $parts[] = $this->ward->type ?? $this->ward->name;
        }
        if ($this->district) {
            $parts[] = $this->district->type ?? $this->district->name;
        }
        if ($this->province) {
            $parts[] = $this->province->type ?? $this->province->name;
        }
        return implode(', ', array_filter($parts));
    }
}
