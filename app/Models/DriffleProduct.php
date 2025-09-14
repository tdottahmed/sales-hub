<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriffleProduct extends Model
{
    protected $guarded = [];

    public function driffleOffers()
    {
        return $this->hasMany(DriffleOffer::class);
    }
}
