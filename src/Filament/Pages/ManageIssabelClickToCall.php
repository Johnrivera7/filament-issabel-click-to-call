<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentIssabelClickToCall\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use JohnRivera7\FilamentIssabelClickToCall\FilamentIssabelClickToCallPlugin;
use JohnRivera7\FilamentIssabelClickToCall\Support\IssabelAmiCredentials;
use UnitEnum;

class ManageIssabelClickToCall extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected string $view = 'filament-issabel-click-to-call::filament.pages.manage-issabel-click-to-call';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return FilamentIssabelClickToCallPlugin::get()->getNavigationGroup();
    }

    public static function getNavigationLabel(): string
    {
        return FilamentIssabelClickToCallPlugin::get()->getNavigationLabel();
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return FilamentIssabelClickToCallPlugin::get()->getNavigationIcon();
    }

    public static function getNavigationSort(): ?int
    {
        return FilamentIssabelClickToCallPlugin::get()->getNavigationSort();
    }

    public function getTitle(): string
    {
        return __('filament-issabel-click-to-call::plugin.page_title');
    }

    public function mount(): void
    {
        $this->form->fill(FilamentIssabelClickToCallPlugin::get()->resolveCredentials()->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament-issabel-click-to-call::plugin.section_ami'))
                    ->description(__('filament-issabel-click-to-call::plugin.section_ami_help'))
                    ->icon(Heroicon::OutlinedPhone)
                    ->schema([
                        Toggle::make('enabled')
                            ->label(__('filament-issabel-click-to-call::plugin.enabled'))
                            ->columnSpanFull(),
                        TextInput::make('host')
                            ->label(__('filament-issabel-click-to-call::plugin.host'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('port')
                            ->label(__('filament-issabel-click-to-call::plugin.port'))
                            ->numeric()
                            ->default(5038)
                            ->required(),
                        TextInput::make('username')
                            ->label(__('filament-issabel-click-to-call::plugin.username'))
                            ->required()
                            ->maxLength(64),
                        TextInput::make('secret')
                            ->label(__('filament-issabel-click-to-call::plugin.secret'))
                            ->password()
                            ->revealable()
                            ->required()
                            ->maxLength(255),
                        Select::make('channel_driver')
                            ->label(__('filament-issabel-click-to-call::plugin.channel_driver'))
                            ->options([
                                'PJSIP' => 'PJSIP (Issabel 4+)',
                                'SIP' => 'SIP (legacy)',
                            ])
                            ->required()
                            ->native(false),
                        TextInput::make('dial_context')
                            ->label(__('filament-issabel-click-to-call::plugin.dial_context'))
                            ->default('from-internal')
                            ->required(),
                        TextInput::make('dial_prefix')
                            ->label(__('filament-issabel-click-to-call::plugin.dial_prefix'))
                            ->maxLength(16),
                        TextInput::make('caller_id_name')
                            ->label(__('filament-issabel-click-to-call::plugin.caller_id_name'))
                            ->maxLength(80)
                            ->columnSpanFull(),
                        TextInput::make('default_extension')
                            ->label(__('filament-issabel-click-to-call::plugin.default_extension'))
                            ->helperText(__('filament-issabel-click-to-call::plugin.default_extension_help'))
                            ->maxLength(16),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament-issabel-click-to-call::plugin.save'))
                ->icon(Heroicon::OutlinedCheck)
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $credentials = IssabelAmiCredentials::fromArray($state);

        FilamentIssabelClickToCallPlugin::get()->persistCredentials($credentials);

        Notification::make()
            ->title(__('filament-issabel-click-to-call::plugin.saved_title'))
            ->success()
            ->send();
    }
}
