<?php

namespace App\Filament\Pages;

use App\Models\FirmSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class FirmSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Configurações do Escritório';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?int $navigationSort     = 99;
    protected static string $view             = 'filament.pages.firm-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(FirmSetting::instance()->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dados do Escritório')
                    ->schema([
                        Forms\Components\TextInput::make('firm_name')
                            ->label('Nome do Escritório')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('firm_email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('firm_phone')
                            ->label('Telefone')
                            ->maxLength(50),
                    ])->columns(3),

                Forms\Components\Section::make('Endereço')
                    ->schema([
                        Forms\Components\TextInput::make('firm_address')
                            ->label('Endereço completo')
                            ->maxLength(255)
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('firm_city')
                            ->label('Cidade')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('firm_state')
                            ->label('UF')
                            ->maxLength(2),
                        Forms\Components\TextInput::make('firm_zipcode')
                            ->label('CEP')
                            ->maxLength(10),
                    ])->columns(3),

                Forms\Components\Section::make('Logo')
                    ->schema([
                        Forms\Components\FileUpload::make('firm_logo')
                            ->label('Logo do Escritório')
                            ->image()
                            ->disk('public')
                            ->directory('firm-logo')
                            ->imagePreviewHeight('120')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Advogados')
                    ->description('Texto que aparecerá na procuração onde estão os dados dos advogados. Use HTML se necessário.')
                    ->schema([
                        Forms\Components\Textarea::make('firm_lawyers')
                            ->label('Parágrafo dos Advogados')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        FirmSetting::instance()->update($data);
        Notification::make()->title('Configurações salvas!')->success()->send();
    }
}