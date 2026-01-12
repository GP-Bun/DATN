<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    // Nếu bảng trong DB tên là 'provinces' thì không cần khai báo $table
    protected $fillable = [
        'code',
        'name',
        'slug',
        'type',
    ];

    /**
     * Một tỉnh/thành phố có nhiều quận/huyện
     */
    public function districts()
    {
        return $this->hasMany(District::class);
    }
}
