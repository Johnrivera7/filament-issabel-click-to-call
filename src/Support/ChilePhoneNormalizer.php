<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentIssabelClickToCall\Support;

final class ChilePhoneNormalizer
{
    /**
     * Normalize a phone for Issabel outbound dial (digits only, optional 56 prefix).
     */
    public static function normalize(?string $phone, bool $withCountryCode = true): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '56') && strlen($digits) >= 11) {
            return $withCountryCode ? $digits : substr($digits, 2);
        }

        if (strlen($digits) === 9 && str_starts_with($digits, '9')) {
            return $withCountryCode ? '56'.$digits : $digits;
        }

        if (strlen($digits) === 8 && str_starts_with($digits, '2')) {
            return $withCountryCode ? '56'.$digits : $digits;
        }

        if (strlen($digits) >= 8) {
            return $withCountryCode && ! str_starts_with($digits, '56')
                ? '56'.$digits
                : $digits;
        }

        return null;
    }
}
