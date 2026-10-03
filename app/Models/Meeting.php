<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'meeting_date',
        'location',
        'category',
        'leader',
        'notewriter',
        'attendees',
        'agenda',
        'summary',
        'action_items',
        'status',
        'attendance_token',
        'is_attendance_open',
        'attachment_path',
        'created_by',
    ];

    protected $casts = [
        'meeting_date' => 'datetime',
        'attendees' => 'array',
        'action_items' => 'array',
        'is_attendance_open' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($meeting) {
            if (empty($meeting->attendance_token)) {
                $meeting->attendance_token = strtolower(\Illuminate\Support\Str::random(12));
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendances()
    {
        return $this->hasMany(MeetingAttendance::class, 'meeting_id')->orderBy('attended_at', 'desc');
    }

    public function ensureAttendanceToken(): string
    {
        if (empty($this->attendance_token)) {
            $this->attendance_token = strtolower(\Illuminate\Support\Str::random(12));
            $this->saveQuietly();
        }
        return $this->attendance_token;
    }
}
