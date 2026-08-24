<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentIssabelClickToCall\Support;

final class IssabelAmiCredentials
{
    public function __construct(
        public bool $enabled,
        public string $host,
        public int $port,
        public string $username,
        public string $secret,
        public int $connectTimeoutSeconds,
        public int $readTimeoutSeconds,
        public string $channelDriver,
        public string $dialContext,
        public string $dialPrefix,
        public string $callerIdName,
        public ?string $defaultExtension,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            enabled: (bool) config('filament-issabel-click-to-call.enabled', true),
            host: (string) config('filament-issabel-click-to-call.host', '127.0.0.1'),
            port: (int) config('filament-issabel-click-to-call.port', 5038),
            username: (string) config('filament-issabel-click-to-call.username', ''),
            secret: (string) config('filament-issabel-click-to-call.secret', ''),
            connectTimeoutSeconds: (int) config('filament-issabel-click-to-call.connect_timeout_seconds', 5),
            readTimeoutSeconds: (int) config('filament-issabel-click-to-call.read_timeout_seconds', 10),
            channelDriver: (string) config('filament-issabel-click-to-call.channel_driver', 'PJSIP'),
            dialContext: (string) config('filament-issabel-click-to-call.dial_context', 'from-internal'),
            dialPrefix: (string) config('filament-issabel-click-to-call.dial_prefix', ''),
            callerIdName: (string) config('filament-issabel-click-to-call.caller_id_name', 'Filament Click-to-Call'),
            defaultExtension: filled(config('filament-issabel-click-to-call.default_extension'))
                ? (string) config('filament-issabel-click-to-call.default_extension')
                : null,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            enabled: (bool) ($data['enabled'] ?? true),
            host: (string) ($data['host'] ?? '127.0.0.1'),
            port: (int) ($data['port'] ?? 5038),
            username: (string) ($data['username'] ?? ''),
            secret: (string) ($data['secret'] ?? ''),
            connectTimeoutSeconds: (int) ($data['connect_timeout_seconds'] ?? 5),
            readTimeoutSeconds: (int) ($data['read_timeout_seconds'] ?? 10),
            channelDriver: (string) ($data['channel_driver'] ?? 'PJSIP'),
            dialContext: (string) ($data['dial_context'] ?? 'from-internal'),
            dialPrefix: (string) ($data['dial_prefix'] ?? ''),
            callerIdName: (string) ($data['caller_id_name'] ?? 'Filament Click-to-Call'),
            defaultExtension: filled($data['default_extension'] ?? null)
                ? (string) $data['default_extension']
                : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'host' => $this->host,
            'port' => $this->port,
            'username' => $this->username,
            'secret' => $this->secret,
            'connect_timeout_seconds' => $this->connectTimeoutSeconds,
            'read_timeout_seconds' => $this->readTimeoutSeconds,
            'channel_driver' => $this->channelDriver,
            'dial_context' => $this->dialContext,
            'dial_prefix' => $this->dialPrefix,
            'caller_id_name' => $this->callerIdName,
            'default_extension' => $this->defaultExtension,
        ];
    }

    public function isConfigured(): bool
    {
        return $this->enabled
            && $this->host !== ''
            && $this->username !== ''
            && $this->secret !== '';
    }
}
