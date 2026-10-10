<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ecosystem extends Model
{
    use HasFactory;

    protected $table = 'ecosystems';

    protected $fillable = [
        'name',
        'code',
        'region',
        'lead_institution',
        'pic_name',
        'pic_phone',
        'pic_email',
        'description',
        'status',
    ];

    /**
     * Relasi ke Mitra Lembaga yang tergabung dalam Ekosistem ini
     */
    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class, 'ecosystem_id');
    }
}
