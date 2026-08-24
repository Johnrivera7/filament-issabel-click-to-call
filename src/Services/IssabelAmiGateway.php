<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentIssabelClickToCall\Services;

use JohnRivera7\FilamentIssabelClickToCall\Support\ChilePhoneNormalizer;
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

    public function originate(
        string $extension,
        string $destination,
        ?string $callerIdName = null,
        ?string $callerIdNumber = null,
    ): ?string {
        if (! $this->credentials->isConfigured()) {
            throw new RuntimeException('Issabel AMI credentials are incomplete.');
        }

        $this->connect();
        $this->login();

        $channel = sprintf('%s/%s', $this->credentials->channelDriver, $extension);
        $callerId = $this->buildCallerId($extension, $destination, $callerIdName, $callerIdNumber);
        $actionId = 'ctc-'.bin2hex(random_bytes(8));

        $lines = $this->buildOriginateLines(
            actionId: $actionId,
            channel: $channel,
            extension: $extension,
            destination: $destination,
            callerId: $callerId,
        );

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
     * @return list<string>
     */
    private function buildOriginateLines(
        string $actionId,
        string $channel,
        string $extension,
        string $destination,
        string $callerId,
    ): array {
        $strategy = (string) config('filament-issabel-click-to-call.originate_strategy', 'application_dial');

        $lines = [
            'Action: Originate',
            'ActionID: '.$actionId,
            'Channel: '.$channel,
            'CallerID: '.$callerId,
            'Async: true',
            'Timeout: 30000',
            ...$this->originateVariables($extension, $destination),
        ];

        if ($strategy === 'context_exten') {
            $lines[] = 'Context: '.$this->credentials->dialContext;
            $lines[] = 'Exten: '.$destination;
            $lines[] = 'Priority: 1';

            return $lines;
        }

        $timeout = (int) config('filament-issabel-click-to-call.dial_timeout_seconds', 300);
        $localChannel = sprintf(
            'Local/%s@%s/n,%d,tTr',
            $destination,
            $this->credentials->dialContext,
            $timeout,
        );

        $lines[] = 'Application: Dial';
        $lines[] = 'Data: '.$localChannel;

        return $lines;
    }

    /**
     * @return list<string>
     */
    private function originateVariables(string $extension, string $destination): array
    {
        return [
            'Variable: AMPUSER='.$extension,
            'Variable: __OriginatingExtension='.$extension,
            'Variable: REALCALLERIDNUM='.$extension,
            'Variable: CALLERID(num)='.$extension,
            'Variable: OUTBOUNDNUM='.$destination,
        ];
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

    private function buildCallerId(
        string $extension,
        string $destination,
        ?string $callerIdName,
        ?string $callerIdNumber,
    ): string {
        $mode = $this->credentials->callerIdDisplay;

        $localNumber = ChilePhoneNormalizer::normalize($callerIdNumber ?? $destination, withCountryCode: false)
            ?? $destination;

        $formattedDestination = ChilePhoneNormalizer::formatLocalDisplay($localNumber) ?? $localNumber;

        $number = match ($mode) {
            'extension' => $extension,
            'custom' => $this->credentials->callerIdNumber ?: $extension,
            'destination' => $localNumber,
            default => $extension,
        };

        $name = match ($mode) {
            'agent_to_destination' => $callerIdName ?? sprintf('%s → %s', $extension, $formattedDestination),
            'destination' => $callerIdName ?? $formattedDestination,
            default => $callerIdName ?? $this->credentials->callerIdName,
        };

        return sprintf('"%s" <%s>', str_replace('"', '', $name), $number);
    }
}
