<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductVariation;
use App\Models\DriffleProduct;
use Illuminate\Support\Facades\DB;

class MapProducts extends Command
{
    protected $signature = 'products:map';
    protected $description = 'Map supplier products with Driffle products';

    // Add these class properties for better organization
    protected $currencyCodes = ['PLN', 'EUR', 'USD', 'AED', 'GBP', 'AUD', 'ARS', 'BRL', 'CZK', 'DKK',
        'CHF', 'CLP', 'COP', 'CNY', 'JPY', 'TRY', 'ZAR', 'MXN', 'NOK', 'SEK', 'NZD',
        'INR', 'HKD', 'HUF', 'IDR', 'KRW', 'MYR', 'PHP', 'RON', 'RUB', 'SAR', 'SGD',
        'THB', 'VND', 'BHD', 'KWD', 'OMR', 'PKR', 'LKR', 'EGP', 'QAR'];

    protected $platformKeywords = [
        'steam' => ['steam', 'pc'],
        'playstation' => ['playstation', 'psn', 'ps4', 'ps5'],
        'xbox' => ['xbox', 'xbox live', 'xbox game pass'],
        'nintendo' => ['nintendo', 'switch', 'eshop'],
        'google' => ['google play', 'google'],
        'appstore' => ['appstore', 'itunes'],
        'riot' => ['riot', 'league of legends', 'valorant'],
        'roblox' => ['roblox'],
        'spotify' => ['spotify'],
        'netflix' => ['netflix'],
        'origin' => ['origin', 'ea'],
        'epic' => ['epic games'],
        'battle.net' => ['battle.net', 'battlenet'],
        'amazon' => ['amazon'],
        'uber' => ['uber'],
        'tiktok' => ['tiktok'],
        'discord' => ['discord'],
        'twitch' => ['twitch'],
        'facebook' => ['facebook'],
        'instagram' => ['instagram'],
        'tinder' => ['tinder'],
        'dazn' => ['dazn'],
        'deezer' => ['deezer'],
        'paramount' => ['paramount'],
        'crunchyroll' => ['crunchyroll'],
        'disney' => ['disney'],
        'hbo' => ['hbo'],
        'youtube' => ['youtube'],
        'tiktok' => ['tiktok'],
        'pubg' => ['pubg'],
        'freefire' => ['free fire'],
        'minecraft' => ['minecraft'],
        'fortnite' => ['fortnite'],
        'callofduty' => ['call of duty', 'cod'],
        'ea' => ['ea sports', 'ea'],
        'blizzard' => ['blizzard'],
        'ubisoft' => ['ubisoft'],
        'rockstar' => ['rockstar'],
        'takeaway' => ['talabat', 'just eat', 'uber eats', 'foodpanda', 'deliveroo']
    ];

    public function handle()
    {
        $this->info("Starting product mapping...");

        // Get all unmapped variations
        $variations = ProductVariation::where('is_mapped', false)->get();
        $driffleProducts = DriffleProduct::all();

        $matched = 0;
        $unmatched = 0;

        foreach ($variations as $variation) {
            $bestMatch = null;
            $bestScore = 0;

            foreach ($driffleProducts as $driffle) {
                $score = $this->calculateScore($variation, $driffle);

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $driffle;
                }
            }

            if ($bestMatch && $bestScore >= 60) { // Lowered threshold
                $this->line("✅ Match: {$variation->name} <--> {$bestMatch->title} (score: $bestScore)");

                $variation->update(['is_mapped' => true]);

                DB::table('similar_products')->updateOrInsert(
                    [
                        'product_variation_id' => $variation->id,
                        'driffle_product_id' => $bestMatch->id,
                    ],
                    [
                        'score' => $bestScore,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $matched++;
            } else {
                $this->warn("❌ No match: {$variation->name}");
                $unmatched++;
            }
        }

        $this->info("Product mapping completed. Matched: $matched, Unmatched: $unmatched");
    }

    /**
     * Calculate similarity score between variation and Driffle product
     */
    private function calculateScore($variation, $driffle)
    {
        $score = 0;

        // Normalize both titles for comparison
        $variationTitle = $this->normalizeTitle($variation->name);
        $driffleTitle = $this->normalizeTitle($driffle->title);

        // 1. Brand matching (most important)
        $brandScore = $this->calculateBrandScore($variationTitle, $driffleTitle);
        if ($brandScore < 20) {
            return 0; // No match if brands don't match at all
        }
        $score += $brandScore;

        // 2. Region matching
        $regionScore = $this->calculateRegionScore($variation, $driffle);
        $score += $regionScore;

        // 3. Value matching (face value, points, duration)
        $valueScore = $this->calculateValueScore($variation, $driffleTitle);
        $score += $valueScore;

        // 4. Platform matching
        $platformScore = $this->calculatePlatformScore($variationTitle, $driffleTitle);
        $score += $platformScore;

        return $score;
    }

    /**
     * Calculate brand similarity score
     */
    private function calculateBrandScore($variationTitle, $driffleTitle)
    {
        $score = 0;

        // Extract primary brand names
        $variationBrand = $this->extractPrimaryBrand($variationTitle);
        $driffleBrand = $this->extractPrimaryBrand($driffleTitle);

        if (empty($variationBrand) || empty($driffleBrand)) {
            return 0;
        }

        // Exact match
        if ($variationBrand === $driffleBrand) {
            return 70;
        }

        // Partial match with Levenshtein distance
        $distance = levenshtein($variationBrand, $driffleBrand);
        $maxLength = max(strlen($variationBrand), strlen($driffleBrand));

        if ($distance <= 2) {
            return 60;
        } elseif ($distance <= 4) {
            return 40;
        } elseif ($distance <= 6) {
            return 20;
        }

        // Check if one brand contains the other
        if (strpos($driffleTitle, $variationBrand) !== false ||
            strpos($variationTitle, $driffleBrand) !== false) {
            return 30;
        }

        return 0;
    }

    /**
     * Extract primary brand from title
     */
    private function extractPrimaryBrand($title)
    {
        $title = strtolower($title);

        // Remove common non-brand words but keep important ones
        $title = preg_replace('/\b(gift|card|voucher|digital|key|egift|subscription|recharge|access|points|coins|diamonds|tokens|bonus)\b/i', '', $title);

        // Remove values and currencies
        $title = preg_replace('/\d+[\.,]?\d*\s*('.implode('|', $this->currencyCodes).')?/i', '', $title);

        // Remove content in parentheses
        $title = preg_replace('/\(.*?\)/', '', $title);

        // Remove special characters but keep letters and spaces
        $title = preg_replace('/[^a-z\s]/', '', $title);

        // Trim and clean up spaces
        $title = preg_replace('/\s+/', ' ', $title);
        $title = trim($title);

        return $title;
    }

    /**
     * Calculate region compatibility score
     */
    private function calculateRegionScore($variation, $driffle)
    {
        if (empty($variation->country_code) || empty($driffle->regionName)) {
            return 0;
        }

        $variationRegion = strtoupper($variation->country_code);
        $driffleRegion = strtolower(trim($driffle->regionName));

        $regionMap = [
            'AE' => ['united arab emirates', 'uae', 'ae'],
            'AT' => ['austria', 'at'],
            'AU' => ['australia', 'au'],
            'AR' => ['argentina', 'ar', 'ars'],
            'PL' => ['poland', 'pl', 'pln'],
            'DE' => ['germany', 'de'],
            'FR' => ['france', 'fr'],
            'US' => ['united states', 'usa', 'america', 'us'],
            'GB' => ['united kingdom', 'uk', 'england', 'gb'],
            'BE' => ['belgium', 'be'],
            'BH' => ['bahrain', 'bh'],
            'BR' => ['brazil', 'br', 'brl'],
            'CA' => ['canada', 'ca', 'cad'],
            'CH' => ['switzerland', 'ch', 'chf'],
            'CL' => ['chile', 'cl', 'clp'],
            'CN' => ['china', 'cn', 'cny'],
            'CO' => ['colombia', 'co', 'cop'],
            'CZ' => ['czech republic', 'czech', 'cz', 'czk'],
            'DK' => ['denmark', 'dk', 'dkk'],
            'EG' => ['egypt', 'eg'],
            'ES' => ['spain', 'es'],
            'FI' => ['finland', 'fi'],
            'GR' => ['greece', 'gr'],
            'HK' => ['hong kong', 'hk'],
            'HU' => ['hungary', 'hu'],
            'ID' => ['indonesia', 'id'],
            'IE' => ['ireland', 'ie'],
            'IL' => ['israel', 'il'],
            'IN' => ['india', 'in'],
            'IT' => ['italy', 'it'],
            'JP' => ['japan', 'jp', 'jpy'],
            'KR' => ['korea', 'kr'],
            'KW' => ['kuwait', 'kw'],
            'MX' => ['mexico', 'mx', 'mxn'],
            'MY' => ['malaysia', 'my'],
            'NL' => ['netherlands', 'nl'],
            'NO' => ['norway', 'no', 'nok'],
            'NZ' => ['new zealand', 'nz', 'nzd'],
            'PH' => ['philippines', 'ph'],
            'PT' => ['portugal', 'pt'],
            'QA' => ['qatar', 'qa'],
            'RU' => ['russia', 'ru'],
            'SA' => ['saudi arabia', 'sa'],
            'SE' => ['sweden', 'se', 'sek'],
            'SG' => ['singapore', 'sg'],
            'TH' => ['thailand', 'th'],
            'TR' => ['turkey', 'tr', 'try'],
            'TW' => ['taiwan', 'tw'],
            'ZA' => ['south africa', 'za', 'zar'],
            'GLOBAL' => ['global', 'world', 'international', 'worldwide']
        ];

        // Check for exact region match
        if (isset($regionMap[$variationRegion])) {
            foreach ($regionMap[$variationRegion] as $pattern) {
                if (strpos($driffleRegion, $pattern) !== false) {
                    return 30; // Strong region match
                }
            }
        }

        // Check for global products
        foreach ($regionMap['GLOBAL'] as $pattern) {
            if (strpos($driffleRegion, $pattern) !== false) {
                return 20; // Global product match
            }
        }

        return -10; // Region mismatch penalty
    }

    /**
     * Calculate value compatibility score
     */
    private function calculateValueScore($variation, $driffleTitle)
    {
        $score = 0;

        // Extract values from both titles
        $variationValue = $this->extractValue($variation->name);
        $driffleValue = $this->extractValue($driffleTitle);

        // If no values in either, return neutral score
        if ($variationValue === null && $driffleValue === null) {
            return 0;
        }

        // If variation has value constraints, check them
        if ($variation->min_face_value !== null && $variation->max_face_value !== null) {
            if ($driffleValue !== null &&
                $driffleValue >= $variation->min_face_value &&
                $driffleValue <= $variation->max_face_value) {
                return 50; // Strong value match
            } else {
                return -30; // Value mismatch penalty
            }
        }

        // If only Driffle has a value, check if it's reasonable
        if ($driffleValue !== null && $variationValue === null) {
            // Many gift cards have values between 5-500, so this is likely acceptable
            if ($driffleValue >= 5 && $driffleValue <= 500) {
                return 10;
            }
        }

        // If both have values and they match exactly
        if ($variationValue !== null && $driffleValue !== null && $variationValue == $driffleValue) {
            return 40;
        }

        return 0;
    }

    /**
     * Extract numeric value from title
     */
    private function extractValue($title)
    {
        // Look for currency values like "100 AED", "50 USD", etc.
        if (preg_match('/\b(\d+)\s*('.implode('|', $this->currencyCodes).')\b/i', $title, $matches)) {
            return (int)$matches[1];
        }

        // Look for points patterns like "2800 FC POINTS", "75000 VC", etc.
        if (preg_match('/\b(\d+)\s*(FC POINTS|VC|points|tokens|diamonds|coins|credits|vp)\b/i', $title, $matches)) {
            return (int)$matches[1];
        }

        // Look for duration patterns like "12 Months", "6M", etc.
        if (preg_match('/\b(\d+)\s*(month|months|m|year|years|y)\b/i', $title, $matches)) {
            return (int)$matches[1];
        }

        // Look for standalone numbers that likely represent values
        if (preg_match('/\b(\d{2,5})\b/', $title, $matches)) {
            $value = (int)$matches[1];
            // Only return if it's a reasonable value
            if ($value >= 5 && $value <= 10000) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Calculate platform compatibility score
     */
    private function calculatePlatformScore($variationTitle, $driffleTitle)
    {
        $variationPlatform = null;
        $drifflePlatform = null;

        // Detect platform in variation title
        foreach ($this->platformKeywords as $platform => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($variationTitle, $keyword) !== false) {
                    $variationPlatform = $platform;
                    break 2;
                }
            }
        }

        // Detect platform in Driffle title
        foreach ($this->platformKeywords as $platform => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($driffleTitle, $keyword) !== false) {
                    $drifflePlatform = $platform;
                    break 2;
                }
            }
        }

        // If both have platforms and they match
        if ($variationPlatform && $drifflePlatform && $variationPlatform === $drifflePlatform) {
            return 20;
        }

        // If one has a platform but the other doesn't, no penalty
        if ((!$variationPlatform && $drifflePlatform) || ($variationPlatform && !$drifflePlatform)) {
            return 0;
        }

        // If platforms are different
        if ($variationPlatform && $drifflePlatform && $variationPlatform !== $drifflePlatform) {
            return -15;
        }

        return 0;
    }

    /**
     * Normalize title for better comparison
     */
    private function normalizeTitle($title)
    {
        $title = strtolower($title);

        // Standardize common patterns
        $title = str_replace(['+', '&', '/'], ' ', $title);

        // Remove special characters but keep letters, numbers and spaces
        $title = preg_replace('/[^a-z0-9\s]/', '', $title);

        // Trim and clean up spaces
        $title = preg_replace('/\s+/', ' ', $title);
        $title = trim($title);

        return $title;
    }
}
