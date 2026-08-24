<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentIssabelClickToCall\Services;

use JohnRivera7\FilamentIssabelClickToCall\Support\ChilePhoneNormalizer;
use JohnRivera7\FilamentIssabelClickToCall\Support\IssabelAmiCredentials;
use RuntimeException;

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

        $actionId = $this->gateway->originate(
            extension: $extension,
            destination: $destination,
            callerIdName: $callerIdName,
            callerIdNumber: $localNumber,
        );

        return [
            'action_id' => $actionId,
            'extension' => $extension,
            'destination' => $destination,
        ];
    }
}
