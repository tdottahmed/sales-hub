<?php

namespace App\Services;

use App\Models\Currency;
use InvalidArgumentException;

class CurrencyConversionService
{
    public function convertToEuro(float $amount, string $currency): float
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount must be positive');
        }

        if (empty($currency)) {
            throw new InvalidArgumentException('Currency code cannot be empty');
        }

        $currency = strtoupper($currency);

        // If already EUR, no conversion
        if ($currency === 'EUR') {
            return round($amount, 2);
        }

        // Get rate from DB
        $rate = Currency::where('code', $currency)->value('rate_to_eur');

        if (!$rate) {
            throw new InvalidArgumentException("Currency code {$currency} not found in DB");
        }

        return round($amount * $rate, 2);
    }
}
