<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SarParticipation extends Model
{
    use HasFactory;

    protected $fillable = [
        'sar_operation_id',
        'user_id',
        'organization_name',
        'commander_name',
        'contact_number',
        'personnel_count',
        'status',
        'departure_location',
        'latitude',
        'longitude',
        'resources_deployed',
        'preparation_notes',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'personnel_count' => 'integer',
    ];

    public function operation()
    {
        return $this->belongsTo(SarOperation::class, 'sar_operation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
