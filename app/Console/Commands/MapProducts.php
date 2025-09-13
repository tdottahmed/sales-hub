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

    public function handle()
    {
        $this->info("Starting product mapping...");

        // Get all unmapped variations
        $variations = ProductVariation::where('is_mapped', false)->pluck('name')->take(500)->implode(', ');
        dd($variations);
        $driffleProducts = DriffleProduct::all();

        foreach ($variations as $variation) {
            $bestMatch = null;
            $bestScore = 0;
            $minScore = 80; // Minimum threshold for a match

            foreach ($driffleProducts as $driffle) {
                $score = $this->calculateScore($variation, $driffle);

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $driffle;
                }
            }

            if ($bestMatch && $bestScore >= $minScore) {
                $this->line("✅ Candidate: {$variation->name} <--> {$bestMatch->title} (score: $bestScore)");

                // Mark variation as mapped
                $variation->update(['is_mapped' => true]);

                // Insert mapping into similar_products table
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
            } else {
                $this->warn("❌ No match found: {$variation->name}");
            }
        }

        $this->info("Product mapping completed.");
    }

    /**
     * Calculate similarity score between variation and Driffle product
     */
    private function calculateScore($variation, $driffle)
    {
        $score = 0;

        // 1. Brand/Title matching (most important)
        $titleScore = $this->calculateTitleScore($variation->name, $driffle->title);
        if ($titleScore === 0) {
            return 0; // No point continuing if titles don't match at all
        }
        $score += $titleScore;

        // 2. Region matching
        if (!empty($variation->country_code) && !empty($driffle->regionName)) {
            if ($this->matchRegion($variation->country_code, $driffle->regionName)) {
                $score += 30;
            } else {
                // Penalize region mismatch but don't completely reject
                $score -= 20;
            }
        }

        // 3. Face value matching (if applicable)
        $faceValueScore = $this->calculateFaceValueScore($variation, $driffle->title);
        $score += $faceValueScore;

        // 4. Platform matching (if applicable)
        $platformScore = $this->calculatePlatformScore($variation->name, $driffle->title);
        $score += $platformScore;

        return $score;
    }

    /**
     * Calculate title similarity score
     */
    private function calculateTitleScore($variationTitle, $driffleTitle)
    {
        // Extract main brands from both titles
        $variationBrand = $this->extractBrand($variationTitle);
        $driffleBrand = $this->extractBrand($driffleTitle);

        // Check if brands match
        if (empty($variationBrand) || empty($driffleBrand)) {
            return 0;
        }

        // Use Levenshtein distance for brand matching
        $distance = levenshtein($variationBrand, $driffleBrand);
        $maxLength = max(strlen($variationBrand), strlen($driffleBrand));

        if ($distance <= 2 || $maxLength <= 3 && $distance === 0) {
            return 70; // Strong match
        } elseif ($distance <= 4) {
            return 50; // Moderate match
        } elseif ($distance <= 6) {
            return 30; // Weak match
        }

        return 0; // No match
    }

    /**
     * Extract the main brand from a title
     */
    private function extractBrand($title)
    {
        $title = strtolower($title);

        // Remove common non-brand words
        $title = preg_replace('/\b(subscription|gift|card|voucher|digital|key|egift|pln|eur|usd|aed|gbp|aud|ars)\b/i', '', $title);

        // Remove values and currencies
        $title = preg_replace('/\d+[\.,]?\d*\s*(month|months|year|years|pln|eur|usd|aed|gbp|aud|ars)?/i', '', $title);

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
     * Calculate face value score
     */
    private function calculateFaceValueScore($variation, $driffleTitle)
    {
        // Extract face value from Driffle title
        $driffleFaceValue = $this->extractFaceValue($driffleTitle);

        // If no face value in Driffle title, skip this check
        if ($driffleFaceValue === null) {
            return 0;
        }

        // If variation has face value constraints, check them
        if ($variation->min_face_value !== null && $variation->max_face_value !== null) {
            if ($driffleFaceValue >= $variation->min_face_value &&
                $driffleFaceValue <= $variation->max_face_value) {
                return 50; // Strong match for face value
            } else {
                return -50; // Penalize for face value mismatch
            }
        }

        return 0;
    }

    /**
     * Extract face value from title
     */
    private function extractFaceValue($title)
    {
        // Look for patterns like "100 AED", "50 USD", etc.
        if (preg_match('/\b(\d+)\s*(PLN|EUR|USD|AED|GBP|AUD|ARS)\b/i', $title, $matches)) {
            return (int)$matches[1];
        }

        // Look for standalone numbers that likely represent values
        if (preg_match('/\b(\d{2,4})\b/', $title, $matches)) {
            $value = (int)$matches[1];
            // Only return if it's a reasonable gift card value
            if ($value >= 5 && $value <= 500) {
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
        $platforms = [
            'steam' => ['steam', 'pc'],
            'playstation' => ['playstation', 'psn', 'ps4', 'ps5'],
            'xbox' => ['xbox', 'xbox live'],
            'nintendo' => ['nintendo', 'switch'],
            'google' => ['google play', 'google'],
            'appstore' => ['appstore', 'itunes'],
            'riot' => ['riot', 'league of legends'],
            'roblox' => ['roblox']
        ];

        $variationPlatform = null;
        $drifflePlatform = null;

        // Detect platform in variation title
        foreach ($platforms as $platform => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($variationTitle, $keyword) !== false) {
                    $variationPlatform = $platform;
                    break 2;
                }
            }
        }

        // Detect platform in Driffle title
        foreach ($platforms as $platform => $keywords) {
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
            return -30;
        }

        return 0;
    }

    private function matchRegion($countryCode, $regionText)
    {
        $map = [
            'AF' => ['afghanistan'],
            'AX' => ['aland islands'],
            'AL' => ['albania'],
            'DZ' => ['algeria'],
            'AS' => ['american samoa'],
            'AD' => ['andorra'],
            'AO' => ['angola'],
            'AI' => ['anguilla'],
            'AQ' => ['antarctica'],
            'AG' => ['antigua and barbuda'],
            'AR' => ['argentina'],
            'AM' => ['armenia'],
            'AW' => ['aruba'],
            'AU' => ['australia'],
            'AT' => ['austria'],
            'AZ' => ['azerbaijan'],
            'BS' => ['bahamas'],
            'BH' => ['bahrain'],
            'BD' => ['bangladesh'],
            'BB' => ['barbados'],
            'BY' => ['belarus'],
            'BE' => ['belgium'],
            'BZ' => ['belize'],
            'BJ' => ['benin'],
            'BM' => ['bermuda'],
            'BT' => ['bhutan'],
            'BO' => ['bolivia'],
            'BA' => ['bosnia and herzegovina'],
            'BW' => ['botswana'],
            'BV' => ['bouvet island'],
            'BR' => ['brazil'],
            'IO' => ['british indian ocean territory'],
            'BN' => ['brunei darussalam'],
            'BG' => ['bulgaria'],
            'BF' => ['burkina faso'],
            'BI' => ['burundi'],
            'KH' => ['cambodia'],
            'CM' => ['cameroon'],
            'CA' => ['canada'],
            'CV' => ['cape verde'],
            'KY' => ['cayman islands'],
            'CF' => ['central african republic'],
            'TD' => ['chad'],
            'CL' => ['chile'],
            'CN' => ['china'],
            'CX' => ['christmas island'],
            'CC' => ['cocos (keeling) islands'],
            'CO' => ['colombia'],
            'KM' => ['comoros'],
            'CG' => ['congo'],
            'CD' => ['congo, democratic republic'],
            'CK' => ['cook islands'],
            'CR' => ['costa rica'],
            'CI' => ['cote d\'ivoire'],
            'HR' => ['croatia'],
            'CU' => ['cuba'],
            'CY' => ['cyprus'],
            'CZ' => ['czech republic'],
            'DK' => ['denmark'],
            'DJ' => ['djibouti'],
            'DM' => ['dominica'],
            'DO' => ['dominican republic'],
            'EC' => ['ecuador'],
            'EG' => ['egypt'],
            'SV' => ['el salvador'],
            'GQ' => ['equatorial guinea'],
            'ER' => ['eritrea'],
            'EE' => ['estonia'],
            'ET' => ['ethiopia'],
            'FK' => ['falkland islands (malvinas)'],
            'FO' => ['faroe islands'],
            'FJ' => ['fiji'],
            'FI' => ['finland'],
            'FR' => ['france'],
            'GF' => ['french guiana'],
            'PF' => ['french polynesia'],
            'TF' => ['french southern territories'],
            'GA' => ['gabon'],
            'GM' => ['gambia'],
            'GE' => ['georgia'],
            'DE' => ['germany'],
            'GH' => ['ghana'],
            'GI' => ['gibraltar'],
            'GR' => ['greece'],
            'GL' => ['greenland'],
            'GD' => ['grenada'],
            'GP' => ['guadeloupe'],
            'GU' => ['guam'],
            'GT' => ['guatemala'],
            'GG' => ['guernsey'],
            'GN' => ['guinea'],
            'GW' => ['guinea-bissau'],
            'GY' => ['guyana'],
            'HT' => ['haiti'],
            'HM' => ['heard island & mcdonald islands'],
            'VA' => ['holy see (vatican city state)'],
            'HN' => ['honduras'],
            'HK' => ['hong kong'],
            'HU' => ['hungary'],
            'IS' => ['iceland'],
            'IN' => ['india'],
            'ID' => ['indonesia'],
            'IR' => ['iran'],
            'IQ' => ['iraq'],
            'IE' => ['ireland'],
            'IM' => ['isle of man'],
            'IL' => ['israel'],
            'IT' => ['italy'],
            'JM' => ['jamaica'],
            'JP' => ['japan'],
            'JE' => ['jersey'],
            'JO' => ['jordan'],
            'KZ' => ['kazakhstan'],
            'KE' => ['kenya'],
            'KI' => ['kiribati'],
            'KR' => ['korea'],
            'KW' => ['kuwait'],
            'KG' => ['kyrgyzstan'],
            'LA' => ['lao people\'s democratic republic'],
            'LV' => ['latvia'],
            'LB' => ['lebanon'],
            'LS' => ['lesotho'],
            'LR' => ['liberia'],
            'LY' => ['libyan arab jamahiriya'],
            'LI' => ['liechtenstein'],
            'LT' => ['lithuania'],
            'LU' => ['luxembourg'],
            'MO' => ['macao'],
            'MK' => ['macedonia'],
            'MG' => ['madagascar'],
            'MW' => ['malawi'],
            'MY' => ['malaysia'],
            'MV' => ['maldives'],
            'ML' => ['mali'],
            'MT' => ['malta'],
            'MH' => ['marshall islands'],
            'MQ' => ['martinique'],
            'MR' => ['mauritania'],
            'MU' => ['mauritius'],
            'YT' => ['mayotte'],
            'MX' => ['mexico'],
            'FM' => ['micronesia, federated states of'],
            'MD' => ['moldova'],
            'MC' => ['monaco'],
            'MN' => ['mongolia'],
            'ME' => ['montenegro'],
            'MS' => ['montserrat'],
            'MA' => ['morocco'],
            'MZ' => ['mozambique'],
            'MM' => ['myanmar'],
            'NA' => ['namibia'],
            'NR' => ['nauru'],
            'NP' => ['nepal'],
            'NL' => ['netherlands'],
            'AN' => ['netherlands antilles'],
            'NC' => ['new caledonia'],
            'NZ' => ['new zealand'],
            'NI' => ['nicaragua'],
            'NE' => ['niger'],
            'NG' => ['nigeria'],
            'NU' => ['niue'],
            'NF' => ['norfolk island'],
            'MP' => ['northern mariana islands'],
            'NO' => ['norway'],
            'OM' => ['oman'],
            'PK' => ['pakistan'],
            'PW' => ['palau'],
            'PS' => ['palestinian territory, occupied'],
            'PA' => ['panama'],
            'PG' => ['papua new guinea'],
            'PY' => ['paraguay'],
            'PE' => ['peru'],
            'PH' => ['philippines'],
            'PN' => ['pitcairn'],
            'PL' => ['poland'],
            'PT' => ['portugala'],
            'PR' => ['puerto rico'],
            'QA' => ['qatar'],
            'RE' => ['reunion'],
            'RO' => ['romania'],
            'RU' => ['russian federation', 'russia'],
            'RW' => ['rwanda'],
            'BL' => ['saint barthelemy'],
            'SH' => ['saint helena'],
            'KN' => ['saint kitts and nevis'],
            'LC' => ['saint lucia'],
            'MF' => ['saint martin'],
            'PM' => ['saint pierre and miquelon'],
            'VC' => ['saint vincent and grenadines'],
            'WS' => ['samoa'],
            'SM' => ['san marino'],
            'ST' => ['sao tome and principe'],
            'SA' => ['saudi arabia'],
            'SN' => ['senegal'],
            'RS' => ['serbia'],
            'SC' => ['seychelles'],
            'SL' => ['sierra leone'],
            'SG' => ['singapore'],
            'SK' => ['slovakia'],
            'SI' => ['slovenia'],
            'SB' => ['solomon islands'],
            'SO' => ['somalia'],
            'ZA' => ['south africa'],
            'GS' => ['south georgia and sandwich isl.'],
            'ES' => ['spain'],
            'LK' => ['sri lanka'],
            'SD' => ['sudan'],
            'SR' => ['suriname'],
            'SJ' => ['svalbard and jan mayen'],
            'SZ' => ['swaziland'],
            'SE' => ['sweden'],
            'CH' => ['switzerland'],
            'SY' => ['syrian arab republic'],
            'TW' => ['taiwan'],
            'TJ' => ['tajikistan'],
            'TZ' => ['tanzania'],
            'TH' => ['thailand'],
            'TL' => ['timor-leste'],
            'TG' => ['togo'],
            'TK' => ['tokelau'],
            'TO' => ['tonga'],
            'TT' => ['trinidad and tobago'],
            'TN' => ['tunisia'],
            'TR' => ['turkey'],
            'TM' => ['turkmenistan'],
            'TC' => ['turks and caicos islands'],
            'TV' => ['tuvalu'],
            'UG' => ['uganda'],
            'UA' => ['ukraine'],
            'AE' => ['united arab emirates', 'uae'],
            'GB' => ['united kingdom', 'uk', 'england'],
            'US' => ['united states', 'usa', 'america'],
            'UM' => ['united states outlying islands'],
            'UY' => ['uruguay'],
            'UZ' => ['uzbekistan'],
            'VU' => ['vanuatu'],
            'VE' => ['venezuela'],
            'VN' => ['vietnam'],
            'VG' => ['virgin islands, british'],
            'VI' => ['virgin islands, u.s.'],
            'WF' => ['wallis and futuna'],
            'EH' => ['western sahara'],
            'YE' => ['yemen'],
            'ZM' => ['zambia'],
            'ZW' => ['zimbabwe']
        ];

        $regionText = strtolower(trim($regionText));

        if (isset($map[$countryCode])) {
            foreach ($map[$countryCode] as $pattern) {
                if (strpos($regionText, $pattern) !== false) {
                    return true;
                }
            }
        }

        // Special cases
        $globalPatterns = ['global', 'world', 'international'];
        foreach ($globalPatterns as $pattern) {
            if (strpos($regionText, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }
}
