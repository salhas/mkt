<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MktProfile extends Model
{
    protected $guarded = [];

    protected $casts = [
        'bank_accounts' => 'array',
    ];
}
