<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form->schema([
            $this->getNameFormComponent(),
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),

            Section::make('Notificações por e-mail')
                ->schema([
                    Toggle::make('notify_email')
                        ->label('Receber notificações por e-mail'),
                    Toggle::make('notify_email_deadlines')
                        ->label('E-mail de prazos')
                        ->visible(fn (Get $get) => $get('notify_email')),
                    Toggle::make('notify_email_hearings')
                        ->label('E-mail de audiências')
                        ->visible(fn (Get $get) => $get('notify_email')),
                    Toggle::make('notify_email_tasks')
                        ->label('E-mail de tarefas')
                        ->visible(fn (Get $get) => $get('notify_email')),
                ]),
        ]);
    }
}
