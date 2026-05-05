<?php

namespace App\Filament\Pages;

use App\Models\FirmSetting;
use App\Services\HolidayService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class FirmSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Configurações do Escritório';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?int    $navigationSort  = 99;
    protected static string  $view            = 'filament.pages.firm-settings';

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

                // ── Feriados ──────────────────────────────────────
                Forms\Components\Section::make('Feriados no Calendário')
                    ->description('Feriados nacionais são exibidos automaticamente. Configure abaixo os feriados estaduais e municipais.')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([

                        Forms\Components\Select::make('holiday_states')
                            ->label('Estados')
                            ->options([
                                'AC' => 'Acre',
                                'AL' => 'Alagoas',
                                'AP' => 'Amapá',
                                'AM' => 'Amazonas',
                                'BA' => 'Bahia',
                                'CE' => 'Ceará',
                                'DF' => 'Distrito Federal',
                                'ES' => 'Espírito Santo',
                                'GO' => 'Goiás',
                                'MA' => 'Maranhão',
                                'MT' => 'Mato Grosso',
                                'MS' => 'Mato Grosso do Sul',
                                'MG' => 'Minas Gerais',
                                'PA' => 'Pará',
                                'PB' => 'Paraíba',
                                'PR' => 'Paraná',
                                'PE' => 'Pernambuco',
                                'PI' => 'Piauí',
                                'RJ' => 'Rio de Janeiro',
                                'RN' => 'Rio Grande do Norte',
                                'RS' => 'Rio Grande do Sul',
                                'RO' => 'Rondônia',
                                'RR' => 'Roraima',
                                'SC' => 'Santa Catarina',
                                'SP' => 'São Paulo',
                                'SE' => 'Sergipe',
                                'TO' => 'Tocantins',
                            ])
                            ->multiple()
                            ->searchable()
                            ->live()                          // ← ao mudar estados, atualiza lista de municípios
                            ->placeholder('Selecione os estados...')
                            ->helperText('Feriados estaduais dos estados selecionados serão exibidos no calendário.')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('holiday_cities')
                            ->label('Municípios')
                            ->options(fn (Get $get) => HolidayService::getMunicipios(
                                $get('holiday_states') ?? []
                            ))
                            ->multiple()
                            ->searchable()
                            ->placeholder(fn (Get $get) => empty($get('holiday_states'))
                                ? 'Selecione ao menos um estado primeiro...'
                                : 'Selecione os municípios...'
                            )
                            ->helperText('Lista filtrada pelos estados selecionados acima. Requer storage/app/feriados/municipios.json.')
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