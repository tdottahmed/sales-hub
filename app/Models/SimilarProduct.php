<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SimilarProduct extends Model
{
    public function driffleProduct(): HasOne
    {
        return $this->hasOne(DriffleProduct::class, 'id', 'driffle_product_id');
    }

    public function product(): HasOne
    {
        return $this->hasOne(ProductVariation::class, 'id', 'product_variation_id');
    }
}
