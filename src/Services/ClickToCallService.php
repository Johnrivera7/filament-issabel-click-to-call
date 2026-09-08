<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentIssabelClickToCall\Services;

use Illuminate\Support\Facades\Cache;
use JohnRivera7\FilamentIssabelClickToCall\Support\ChilePhoneNormalizer;
use JohnRivera7\FilamentIssabelClickToCall\Support\IssabelAmiCredentials;
use RuntimeException;
use Throwable;

final class ClickToCallService
{
    public function __construct(
        private IssabelAmiGateway $gateway,
    ) {}

    public static function make(?IssabelAmiCredentials $credentials = null): self
    {
        return new self(IssabelAmiGateway::make($credentials));
    }

    /**
     * Originate: ring extension first, then dial destination when answered.
     *
     * @return array{action_id: string|null, extension: string, destination: string}
     */
    public function call(string $extension, ?string $phone, ?string $callerIdName = null): array
    {
        $credentials = $this->gateway->credentials();

        if (! $credentials->isConfigured()) {
            throw new RuntimeException('Issabel AMI is not configured. Set ISSABEL_PBX_* in .env or use the settings page.');
        }

        $extension = trim($extension);
        if ($extension === '') {
            throw new RuntimeException('Extension (anexo) is required for click-to-call.');
        }

        $dialFormat = (string) config('filament-issabel-click-to-call.dial_format', 'local_9');

        $localNumber = ChilePhoneNormalizer::normalize($phone, withCountryCode: false);
        if ($localNumber === null) {
            throw new RuntimeException('Invalid or empty phone number.');
        }

        $destination = ChilePhoneNormalizer::forDial($phone, $dialFormat);
        if ($destination === null) {
            throw new RuntimeException('Invalid or empty phone number.');
        }

        $destination = $credentials->dialPrefix.$destination;

        $this->guardAgainstExtraLegs($extension);

        try {
            $actionId = $this->gateway->originate(
                extension: $extension,
                destination: $destination,
                callerIdName: $callerIdName,
                callerIdNumber: $localNumber,
            );
        } catch (Throwable $e) {
            $this->releaseOriginateLock($extension);

            throw $e;
        }

        return [
            'action_id' => $actionId,
            'extension' => $extension,
            'destination' => $destination,
        ];
    }

    /**
     * True when the extension is ringing, talking or on hold right now.
     */
    public function extensionInCall(string $extension): bool
    {
        return $this->gateway->isExtensionInCall(trim($extension));
    }

    /**
     * Two guards against a second leg on the same extension: an atomic lock for
     * racing clicks, and the live PBX state for a call that is still up.
     */
    private function guardAgainstExtraLegs(string $extension): void
    {
        if (! (bool) config('filament-issabel-click-to-call.prevent_extra_legs', true)) {
            return;
        }

        $seconds = max(1, (int) config('filament-issabel-click-to-call.originate_lock_seconds', 15));

        if (! Cache::add($this->originateLockKey($extension), 1, $seconds)) {
            throw new RuntimeException(__('filament-issabel-click-to-call::plugin.originate_in_progress', [
                'extension' => $extension,
            ]));
        }

        try {
            $inCall = $this->gateway->isExtensionInCall($extension);
        } catch (Throwable $e) {
            $this->releaseOriginateLock($extension);

            throw $e;
        }

        if ($inCall) {
            $this->releaseOriginateLock($extension);

            throw new RuntimeException(__('filament-issabel-click-to-call::plugin.extension_in_call', [
                'extension' => $extension,
            ]));
        }
    }

    private function releaseOriginateLock(string $extension): void
    {
        Cache::forget($this->originateLockKey($extension));
    }

    private function originateLockKey(string $extension): string
    {
        return 'filament-ctc-originate-'.$extension;
    }
}
