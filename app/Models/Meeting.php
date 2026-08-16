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
        'attachment_path',
        'created_by',
    ];

    protected $casts = [
        'meeting_date' => 'datetime',
        'attendees' => 'array',
        'action_items' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
