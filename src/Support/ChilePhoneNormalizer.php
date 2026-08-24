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

    /**
     * Chile mobile display for agent phone (local 9-digit, no country code).
     */
    public static function formatLocalDisplay(?string $phone): ?string
    {
        $local = self::normalize($phone, withCountryCode: false);
        if ($local === null) {
            return null;
        }

        if (strlen($local) === 9 && str_starts_with($local, '9')) {
            return '9 '.substr($local, 1, 4).' '.substr($local, 5);
        }

        return $local;
    }

    /**
     * @deprecated Use formatLocalDisplay() for agent CallerID.
     */
    public static function formatForDisplay(?string $phone): ?string
    {
        return self::formatLocalDisplay($phone);
    }

    /**
     * Number sent to Issabel dialplan (before optional dial_prefix).
     */
    public static function forDial(?string $phone, string $format = 'local_9'): ?string
    {
        $local = self::normalize($phone, withCountryCode: false);
        if ($local === null) {
            return null;
        }

        return match ($format) {
            'e164_cl' => '56'.$local,
            'outside_9' => str_starts_with($local, '9') && strlen($local) === 9
                ? '9'.$local
                : $local,
            'zero_nine' => str_starts_with($local, '9') && strlen($local) === 9
                ? '0'.$local
                : $local,
            default => $local,
        };
    }
}
