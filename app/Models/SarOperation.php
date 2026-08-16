<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SarOperation extends Model
{
    use HasFactory;

    protected $table = 'sar_operations';

    protected $fillable = [
        'code',
        'title',
        'type',
        'location',
        'latitude',
        'longitude',
        'status',
        'severity_level',
        'commander_name',
        'personnel_count',
        'potensi_sar',
        'deployed_teams',
        'standby_teams',
        'start_date',
        'end_date',
        'description',
        'equipment_used',
        'victims_saved',
        'victims_injured',
        'victims_deceased',
        'victims_missing',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'personnel_count' => 'integer',
        'victims_saved' => 'integer',
        'victims_injured' => 'integer',
        'victims_deceased' => 'integer',
        'victims_missing' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function participations()
    {
        return $this->hasMany(SarParticipation::class, 'sar_operation_id');
    }
}
