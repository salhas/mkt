<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logistic extends Model
{
    protected $guarded = [];

    public function transactions()
    {
        return $this->hasMany(LogisticTransaction::class);
    }
}
