<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Volunteer extends Model
{
    protected $guarded = [];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
