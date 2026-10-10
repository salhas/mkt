<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerEquipment extends Model
{
    use HasFactory;

    protected $table = 'partner_equipments';

    protected $fillable = [
        'partner_id',
        'item_code',
        'name',
        'category',
        'quantity',
        'unit',
        'condition',
        'storage_location',
        'ownership_status',
        'status',
        'notes',
        'photo_path',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Relasi ke Mitra Pemilik Peralatan
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }
}
