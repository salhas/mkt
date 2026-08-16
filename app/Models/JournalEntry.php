<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(JournalItem::class);
    }
}
