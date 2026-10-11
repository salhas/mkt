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
        'slug',
        'region',
        'lead_institution',
        'tagline',
        'address',
        'website',
        'instagram',
        'logo_path',
        'banner_path',
        'pic_name',
        'pic_phone',
        'pic_email',
        'description',
        'vision',
        'mission',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (Ecosystem $eco) {
            if (empty($eco->slug)) {
                $baseSlug = \Illuminate\Support\Str::slug($eco->name) ?: 'ekosistem';
                $slug = $baseSlug;
                $counter = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                $eco->slug = $slug;
            }
        });

        static::updating(function (Ecosystem $eco) {
            if (empty($eco->slug)) {
                $baseSlug = \Illuminate\Support\Str::slug($eco->name) ?: 'ekosistem';
                $slug = $baseSlug;
                $counter = 1;
                while (static::where('slug', $slug)->where('id', '!=', $eco->id)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                $eco->slug = $slug;
            }
        });
    }

    /**
     * Relasi ke Mitra Lembaga yang tergabung dalam Ekosistem ini
     */
    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class, 'ecosystem_id');
    }

    /**
     * Relasi ke Relawan yang bernaung di bawah mitra ekosistem ini
     */
    public function volunteers()
    {
        return Volunteer::whereIn('partner_id', $this->partners()->pluck('id'));
    }

    /**
     * Relasi ke Peralatan & Perlengkapan gabungan seluruh mitra dalam ekosistem ini
     */
    public function equipments()
    {
        return PartnerEquipment::whereIn('partner_id', $this->partners()->pluck('id'));
    }
}
