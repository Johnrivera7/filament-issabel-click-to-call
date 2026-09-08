<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentIssabelClickToCall\Support;

/**
 * Asterisk extension/hint state helpers (AMI ExtensionState + CoreShowChannels).
 */
final class AmiExtensionState
{
    public const NOT_FOUND = -1;

    public const IDLE = 0;

    public const IN_USE = 1;

    public const BUSY = 2;

    public const UNAVAILABLE = 4;

    public const RINGING = 8;

    public const ON_HOLD = 16;

    /**
     * Hints are optional on the PBX: -1 means "no hint", not "idle".
     */
    public static function isKnown(int $status): bool
    {
        return $status >= self::IDLE;
    }

    /**
     * States where a new originate would add another leg to the extension.
     */
    public static function occupies(int $status): bool
    {
        if (! self::isKnown($status)) {
            return false;
        }

        return ($status & (self::IN_USE | self::BUSY | self::RINGING | self::ON_HOLD)) !== 0;
    }

    /**
     * @param  array<string, string>  $event  CoreShowChannels event
     */
    public static function channelBelongsTo(array $event, string $extension): bool
    {
        $digits = self::digits($extension);
        if ($digits === '') {
            return false;
        }

        foreach (['Channel', 'DestinationChannel', 'BridgedChannel'] as $key) {
            if (self::channelMatches((string) ($event[$key] ?? ''), $digits)) {
                return true;
            }
        }

        foreach (['CallerIDNum', 'ConnectedLineNum', 'Exten'] as $key) {
            if (self::digits((string) ($event[$key] ?? '')) === $digits) {
                return true;
            }
        }

        return false;
    }

    /**
     * SIP/2150-0000a1, PJSIP/2150-00b2, Local/2150@from-internal-0003;1
     */
    private static function channelMatches(string $channel, string $extensionDigits): bool
    {
        if ($channel === '') {
            return false;
        }

        return preg_match('#/'.preg_quote($extensionDigits, '#').'(?:[-@;]|$)#', $channel) === 1;
    }

    private static function digits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }
}
