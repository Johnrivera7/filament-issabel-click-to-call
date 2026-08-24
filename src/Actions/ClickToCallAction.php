<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentIssabelClickToCall\Actions;

use Closure;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use JohnRivera7\FilamentIssabelClickToCall\FilamentIssabelClickToCallPlugin;
use JohnRivera7\FilamentIssabelClickToCall\Services\ClickToCallService;
use Throwable;

final class ClickToCallAction
{
    /**
     * Table or form action that originates a call via Issabel AMI.
     *
     * @param  Closure(mixed): ?string  $phoneResolver
     * @param  Closure(mixed): ?string|null  $extensionResolver
     */
    public static function make(
        string $name = 'clickToCall',
        ?Closure $phoneResolver = null,
        ?Closure $extensionResolver = null,
    ): Action {
        return Action::make($name)
            ->label(__('filament-issabel-click-to-call::plugin.action_call'))
            ->icon(Heroicon::OutlinedPhone)
            ->color('success')
            ->action(function (mixed $record = null) use ($phoneResolver, $extensionResolver): void {
                $plugin = FilamentIssabelClickToCallPlugin::get();
                $credentials = $plugin->resolveCredentials();

                if (! $credentials->isConfigured()) {
                    Notification::make()
                        ->title(__('filament-issabel-click-to-call::plugin.not_configured_title'))
                        ->body(__('filament-issabel-click-to-call::plugin.not_configured_body'))
                        ->warning()
                        ->send();

                    return;
                }

                $phone = $phoneResolver !== null ? ($phoneResolver)($record) : null;
                $extension = $extensionResolver !== null
                    ? ($extensionResolver)($record)
                    : $credentials->defaultExtension;

                if (blank($extension)) {
                    Notification::make()
                        ->title(__('filament-issabel-click-to-call::plugin.no_extension_title'))
                        ->body(__('filament-issabel-click-to-call::plugin.no_extension_body'))
                        ->warning()
                        ->send();

                    return;
                }

                try {
                    $result = $plugin->clickToCall()->call((string) $extension, $phone !== null ? (string) $phone : null);

                    Notification::make()
                        ->title(__('filament-issabel-click-to-call::plugin.call_started_title'))
                        ->body(__('filament-issabel-click-to-call::plugin.call_started_body', [
                            'extension' => $result['extension'],
                            'destination' => $result['destination'],
                        ]))
                        ->success()
                        ->send();
                } catch (Throwable $e) {
                    report($e);

                    Notification::make()
                        ->title(__('filament-issabel-click-to-call::plugin.call_failed_title'))
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
