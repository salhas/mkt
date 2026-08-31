<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $guarded = [];

    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    protected static function booted(): void
    {
        static::saved(function (Donation $donation) {
            app(\App\Services\DonationAccountingService::class)->syncDonation($donation);
        });

        static::deleted(function (Donation $donation) {
            app(\App\Services\DonationAccountingService::class)->removeDonationJournal($donation);
        });
    }
}
