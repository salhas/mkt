<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogisticTransaction extends Model
{
    protected $guarded = [];

    public function logistic()
    {
        return $this->belongsTo(Logistic::class);
    }
}
