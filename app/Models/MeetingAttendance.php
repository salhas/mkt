<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'name',
        'phone',
        'email',
        'institution',
        'position',
        'signature',
        'notes',
        'ip_address',
        'user_agent',
        'attended_at',
    ];

    protected $casts = [
        'attended_at' => 'datetime',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }
}
