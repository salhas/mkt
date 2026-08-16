<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationMember extends Model
{
    protected $fillable = [
        'member_number',
        'name',
        'tier',
        'position',
        'division',
        'email',
        'phone',
        'address',
        'photo_path',
        'status',
        'period',
        'order_index',
        'notes',
        'user_id',
        'volunteer_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function volunteer(): BelongsTo
    {
        return $this->belongsTo(Volunteer::class);
    }
}
