<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriffleOffer extends Model
{
    protected $guarded = [];

    public function driffleProduct():belongsTo
    {
        return $this->belongsTo(DriffleProduct::class, 'driffle_product_id');
    }
}
