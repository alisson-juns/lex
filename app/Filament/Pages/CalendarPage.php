<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Page;

class CalendarPage extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-s-calendar-days';
    protected static ?string $title           = 'Calendário';
    protected static ?string $navigationLabel = 'Calendário';
    protected static ?string $navigationGroup = 'Agenda';
    protected static ?int    $navigationSort  = 99;
    protected static string  $view            = 'filament.pages.calendar-page';

    protected function getHeaderActions(): array
    {
        $connected = auth()->user()?->googleToken !== null;

        return [
            Action::make('google')
                ->label($connected ? 'Desconectar Google Calendar' : 'Conectar Google Calendar')
                ->icon($connected ? 'heroicon-o-x-circle' : 'heroicon-o-calendar-days')
                ->color($connected ? 'danger' : 'success')
                ->outlined()
                ->when(
                    $connected,
                    fn ($a) => $a
                    ->requiresConfirmation()
                    ->modalHeading('Desconectar Google Calendar')
                    ->modalDescription('Eventos do Google não aparecerão mais. Audiências e tarefas existentes mantêm o vínculo.')
                    ->modalSubmitActionLabel('Desconectar')
                )
                ->url($connected ? '/google/disconnect' : '/google/redirect'),
        ];
    }
}
