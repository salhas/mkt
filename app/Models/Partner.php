<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends Model
{
    protected $fillable = [
        'code',
        'name',
        'category',
        'pic_name',
        'pic_phone',
        'pic_email',
        'phone',
        'email',
        'address',
        'logo_path',
        'status',
        'mou_number',
        'personnel_count',
        'description',
    ];

    public function volunteers(): HasMany
    {
        return $this->hasMany(Volunteer::class);
    }
}
