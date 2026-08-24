<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentIssabelClickToCall\Services;

use JohnRivera7\FilamentIssabelClickToCall\Support\IssabelAmiCredentials;
use RuntimeException;

final class IssabelAmiGateway
{
    /** @var resource|null */
    private $socket = null;

    public function __construct(
        private IssabelAmiCredentials $credentials,
    ) {}

    public static function make(?IssabelAmiCredentials $credentials = null): self
    {
        return new self($credentials ?? IssabelAmiCredentials::fromConfig());
    }

    public function credentials(): IssabelAmiCredentials
    {
        return $this->credentials;
    }

    public function originate(string $extension, string $destination, ?string $callerIdName = null): ?string
    {
        if (! $this->credentials->isConfigured()) {
            throw new RuntimeException('Issabel AMI credentials are incomplete.');
        }

        $this->connect();
        $this->login();

        $channel = sprintf('%s/%s', $this->credentials->channelDriver, $extension);
        $callerId = sprintf('"%s" <%s>', str_replace('"', '', $callerIdName ?? $this->credentials->callerIdName), $extension);
        $actionId = 'ctc-'.bin2hex(random_bytes(8));

        $lines = [
            'Action: Originate',
            'ActionID: '.$actionId,
            'Channel: '.$channel,
            'Context: '.$this->credentials->dialContext,
            'Exten: '.$destination,
            'Priority: 1',
            'CallerID: '.$callerId,
            'Async: true',
            'Timeout: 30000',
        ];

        $response = $this->sendAction($lines);
        $this->logout();
        $this->disconnect();

        if (($response['Response'] ?? '') !== 'Success') {
            $message = $response['Message'] ?? 'AMI Originate failed';

            throw new RuntimeException((string) $message);
        }

        return $actionId;
    }

    /**
     * @param  list<string>  $lines
     * @return array<string, string>
     */
    public function sendAction(array $lines): array
    {
        if (! is_resource($this->socket)) {
            throw new RuntimeException('AMI socket is not connected.');
        }

        $payload = implode("\r\n", $lines)."\r\n\r\n";
        $written = fwrite($this->socket, $payload);
        if ($written === false) {
            throw new RuntimeException('Failed to write to AMI socket.');
        }

        return $this->readResponse();
    }

    public function connect(): void
    {
        if (is_resource($this->socket)) {
            return;
        }

        $errno = 0;
        $errstr = '';
        $socket = @fsockopen(
            $this->credentials->host,
            $this->credentials->port,
            $errno,
            $errstr,
            $this->credentials->connectTimeoutSeconds,
        );

        if ($socket === false) {
            throw new RuntimeException(sprintf(
                'Cannot connect to Issabel AMI at %s:%d (%s).',
                $this->credentials->host,
                $this->credentials->port,
                $errstr !== '' ? $errstr : 'error '.$errno,
            ));
        }

        stream_set_timeout($socket, $this->credentials->readTimeoutSeconds);
        $this->socket = $socket;

        // Greeting banner (Asterisk Call Manager...)
        $this->readResponse();
    }

    public function login(): void
    {
        $response = $this->sendAction([
            'Action: Login',
            'Username: '.$this->credentials->username,
            'Secret: '.$this->credentials->secret,
            'Events: off',
        ]);

        if (($response['Response'] ?? '') !== 'Success') {
            throw new RuntimeException($response['Message'] ?? 'AMI login failed.');
        }
    }

    public function logout(): void
    {
        if (! is_resource($this->socket)) {
            return;
        }

        try {
            $this->sendAction(['Action: Logout']);
        } catch (RuntimeException) {
            // ignore on teardown
        }
    }

    public function disconnect(): void
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }

        $this->socket = null;
    }

    /**
     * @return array<string, string>
     */
    private function readResponse(): array
    {
        if (! is_resource($this->socket)) {
            return [];
        }

        $buffer = '';
        while (! feof($this->socket)) {
            $line = fgets($this->socket);
            if ($line === false) {
                break;
            }
            if ($line === "\r\n") {
                break;
            }
            $buffer .= $line;
        }

        $out = [];
        foreach (preg_split("/\r\n|\n|\r/", trim($buffer)) ?: [] as $line) {
            if (! str_contains($line, ':')) {
                continue;
            }
            [$key, $value] = explode(':', $line, 2);
            $out[trim($key)] = trim($value);
        }

        return $out;
    }
}
