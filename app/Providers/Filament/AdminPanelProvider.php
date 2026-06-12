<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use Promethys\Revive\RevivePlugin;
use Rmsramos\Activitylog\ActivitylogPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        FilamentAsset::register([
        Css::make('fullcalendar-custom', base_path('resources/css/fullcalendar-custom.css')),    ]);

    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('user')
            ->login()
            ->profile(isSimple: false)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
                \App\Filament\Pages\FirmSettings::class,
                \App\Filament\Pages\RecycleBinPage::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([


            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
                FilamentFullCalendarPlugin::make()
                    ->selectable(true)
                    ->timezone(config('app.timezone'))
                    ->locale('pt-br'),

                ActivitylogPlugin::make()
                ->resource(\App\Filament\Resources\CustomActivitylogResource::class)
                ->label('Log de Atividade')
                ->pluralLabel('Logs de Atividade')
                ->navigationGroup('Sistema')
                ->navigationIcon('heroicon-o-shield-check')
                ->navigationSort(99)
                ->authorize(fn () => auth()->user()->hasRole(['super_admin', 'admin']))
                ->isRestoreActionHidden(false)
                ->isRestoreModelActionHidden(false)
                ->resource(\App\Filament\Resources\CustomActivitylogResource::class) // <- novo
                ->translateLogKey(
                    fn ($label) => __('activitylog_keys.' . $label) !== 'activitylog_keys.' . $label // <- novo
                    ? __('activitylog_keys.' . $label)
                    : $label
                ),

            ])

            ->assets([
            \Filament\Support\Assets\Css::make('widget-cards', resource_path('css/widgets/cards.css')),
            ])

            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
