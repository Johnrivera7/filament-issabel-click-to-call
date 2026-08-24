<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentIssabelClickToCall;

use Filament\Contracts\Plugin;
use Filament\Panel;
use JohnRivera7\FilamentIssabelClickToCall\Filament\Pages\ManageIssabelClickToCall;
use JohnRivera7\FilamentIssabelClickToCall\Services\ClickToCallService;
use JohnRivera7\FilamentIssabelClickToCall\Support\IssabelAmiCredentials;

class FilamentIssabelClickToCallPlugin implements Plugin
{
    protected bool $hasSettingsPage = true;

    /** @var (callable(): IssabelAmiCredentials)|null */
    protected $credentialsResolver = null;

    /** @var (callable(IssabelAmiCredentials): mixed)|null */
    protected $credentialsPersister = null;

    protected ?string $navigationGroup = null;

    protected ?string $navigationLabel = null;

    protected ?string $navigationIcon = null;

    protected ?int $navigationSort = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'filament-issabel-click-to-call';
    }

    public function register(Panel $panel): void
    {
        if ($this->hasSettingsPage()) {
            $panel->pages([
                ManageIssabelClickToCall::class,
            ]);
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public function settingsPage(bool $condition = true): static
    {
        $this->hasSettingsPage = $condition;

        return $this;
    }

    public function hasSettingsPage(): bool
    {
        return $this->hasSettingsPage && (bool) config('filament-issabel-click-to-call.register_settings_page', true);
    }

    /**
     * @param  callable(): IssabelAmiCredentials  $callback
     */
    public function credentialsUsing(callable $callback): static
    {
        $this->credentialsResolver = $callback;

        return $this;
    }

    /**
     * @param  callable(IssabelAmiCredentials): mixed  $callback
     */
    public function persistCredentialsUsing(callable $callback): static
    {
        $this->credentialsPersister = $callback;

        return $this;
    }

    public function resolveCredentials(): IssabelAmiCredentials
    {
        if ($this->credentialsResolver !== null) {
            return ($this->credentialsResolver)();
        }

        return IssabelAmiCredentials::fromConfig();
    }

    public function persistCredentials(IssabelAmiCredentials $credentials): void
    {
        if ($this->credentialsPersister !== null) {
            ($this->credentialsPersister)($credentials);
        }
    }

    public function clickToCall(?IssabelAmiCredentials $credentials = null): ClickToCallService
    {
        return ClickToCallService::make($credentials ?? $this->resolveCredentials());
    }

    public function navigationGroup(?string $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function getNavigationGroup(): ?string
    {
        return $this->navigationGroup ?? config('filament-issabel-click-to-call.navigation.group');
    }

    public function navigationLabel(?string $label): static
    {
        $this->navigationLabel = $label;

        return $this;
    }

    public function getNavigationLabel(): string
    {
        return $this->navigationLabel
            ?? (string) config('filament-issabel-click-to-call.navigation.label', 'Issabel Click-to-Call');
    }

    public function navigationIcon(?string $icon): static
    {
        $this->navigationIcon = $icon;

        return $this;
    }

    public function getNavigationIcon(): string
    {
        return $this->navigationIcon
            ?? (string) config('filament-issabel-click-to-call.navigation.icon', 'heroicon-o-phone');
    }

    public function navigationSort(?int $sort): static
    {
        $this->navigationSort = $sort;

        return $this;
    }

    public function getNavigationSort(): int
    {
        return $this->navigationSort
            ?? (int) config('filament-issabel-click-to-call.navigation.sort', 50);
    }
}
