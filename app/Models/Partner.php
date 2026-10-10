<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Partner extends Model
{
    protected $fillable = [
        'code',
        'name',
        'slug',
        'category',
        'tagline',
        'pic_name',
        'pic_phone',
        'pic_email',
        'phone',
        'email',
        'website',
        'instagram',
        'facebook',
        'address',
        'logo_path',
        'banner_path',
        'status',
        'mou_number',
        'readiness_status',
        'recruitment_status',
        'personnel_count',
        'description',
        'vision',
        'mission',
        'membership_terms',
        'pillar_pre',
        'pillar_during',
        'pillar_post',
    ];

    protected static function booted(): void
    {
        static::creating(function (Partner $partner) {
            if (empty($partner->slug)) {
                $baseSlug = Str::slug($partner->name) ?: 'mitra';
                $slug = $baseSlug;
                $counter = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                $partner->slug = $slug;
            }
        });

        static::updating(function (Partner $partner) {
            if (empty($partner->slug)) {
                $baseSlug = Str::slug($partner->name) ?: 'mitra';
                $slug = $baseSlug;
                $counter = 1;
                while (static::where('slug', $slug)->where('id', '!=', $partner->id)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                $partner->slug = $slug;
            }
        });
    }

    public function volunteers(): HasMany
    {
        return $this->hasMany(Volunteer::class);
    }

    /**
     * Cari riwayat partisipasi operasi SAR berdasarkan nama organisasi atau afiliasi
     */
    public function getSarParticipations()
    {
        return SarParticipation::with('operation')
            ->where(function ($q) {
                $q->where('organization_name', 'like', "%{$this->name}%")
                  ->orWhere('organization_name', 'like', "%" . preg_replace('/\s*\(.*?\)\s*/', '', $this->name) . "%");
            })
            ->orderBy('id', 'desc')
            ->get();
    }
}
