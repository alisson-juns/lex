<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use App\Models\ClientSpouse;
use App\Enums\CaseStatus;
use App\Models\CourtName;
use App\Models\CourtNumber;
use App\Models\Forum;
use App\Models\Lawyer;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Leandrocfe\FilamentPtbrFormFields\Cep;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;
    protected static ?string $modelLabel = 'Cliente';
    protected static ?string $pluralModelLabel = 'Clientes';
    protected static ?string $navigationGroup = 'Controle de Clientes';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Pessoa Física';
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\ClientResource\RelationManagers\LegalCasesRelationManager::class,
            \App\Filament\Resources\ClientResource\RelationManagers\PowersOfAttorneyRelationManager::class,
            \App\Filament\Resources\ClientResource\RelationManagers\GratuityDeclarationsRelationManager::class,
            ActivitylogRelationManager::class,

        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Dados Pessoais')
                        ->icon('heroicon-m-user')
                        ->description('Informações principais do cliente')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Nome')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\DatePicker::make('date_of_birth')
                                ->label('Data de nascimento'),
                            Forms\Components\Select::make('gender')
                                ->options([
                                    'male' => 'Masculino',
                                    'female' => 'Feminino',
                                    'other' => 'Outro',
                                ])
                                ->label('Gênero'),
                            Forms\Components\TextInput::make('father')
                                ->label('Pai')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('mother')
                                ->label('Mãe')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('place_of_birth')
                                ->label('Naturalidade')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('nationality')
                                ->label('Nacionalidade')
                                ->maxLength(255),
                            Forms\Components\Select::make('marital_status')
                                ->options([
                                    'single' => 'Solteiro(a)',
                                    'married' => 'Casado(a)',
                                    'separated' => 'Separado(a)',
                                    'divorced' => 'Divorciado(a)',
                                    'widowed' => 'Viúvo(a)',
                                ])
                                ->label('Estado civil'),
                            Forms\Components\TextInput::make('profession')
                                ->label('Profissão')
                                ->maxLength(255),
                            Forms\Components\Textarea::make('note')
                                ->label('Observações')
                                ->rows(3)
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

                    Step::make('Documentos')
                        ->icon('heroicon-m-document-text')
                        ->description('Documentação do cliente')
                        ->schema([
                            Forms\Components\Group::make()
                                ->relationship('client_documents')
                                ->schema([
                                    Forms\Components\TextInput::make('cpf')
                                        ->label('CPF')
                                        ->mask('999.999.999-99')
                                        ->maxLength(14)
                                        ->rule('cpf')
                                        ->required()
                                        ->unique('client_documents', 'cpf', ignoreRecord: true)
                                        ->validationMessages([
                                            'required' => 'O campo CPF é obrigatório.',
                                            'cpf' => 'Número de CPF inválido.',
                                            'unique' => 'Este CPF já foi registrado.',
                                        ]),
                                    Forms\Components\TextInput::make('rg')
                                        ->label('RG')
                                        ->mask('99.999.999-9')
                                        ->maxLength(12),
                                    Forms\Components\TextInput::make('cnh')
                                        ->label('CNH')
                                        ->mask('99999999999')
                                        ->rule('cnh')
                                        ->maxLength(11)
                                        ->validationMessages([
                                            'cnh' => 'Número de CNH inválido.',
                                        ]),
                                    Forms\Components\TextInput::make('pis')
                                        ->label('PIS')
                                        ->mask('999.99999.99-9')
                                        ->rule('pis')
                                        ->maxLength(14)
                                        ->validationMessages([
                                            'pis' => 'Número de PIS inválido.',
                                        ]),
                                    Forms\Components\TextInput::make('ctps')
                                        ->label('CTPS')
                                        ->maxLength(20),
                                    Forms\Components\TextInput::make('rnm')
                                        ->label('RNM')
                                        ->maxLength(20),
                                    Forms\Components\Textarea::make('other_documents')
                                        ->label('Outros documentos')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ]),

                    Step::make('Endereço')
                        ->icon('heroicon-m-map-pin')
                        ->description('Correspondência do cliente')
                        ->schema([
                            Forms\Components\Group::make()
                                ->relationship('client_addresses')
                                ->schema([
                                    Cep::make('zipcode')
                                        ->label('CEP')
                                        ->viaCep(
                                            mode: 'suffix',
                                            errorMessage: 'CEP inválido.',
                                            setFields: [
                                                'street' => 'logradouro',
                                                'number' => 'numero',
                                                'complement' => 'complemento',
                                                'district' => 'bairro',
                                                'city' => 'localidade',
                                                'state' => 'uf',
                                            ]
                                        )
                                        ->live(onBlur: true),
                                    Forms\Components\TextInput::make('street')
                                        ->label('Endereço')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('number')
                                        ->label('Número')
                                        ->maxLength(10),
                                    Forms\Components\TextInput::make('complement')
                                        ->label('Complemento')
                                        ->maxLength(50),
                                    Forms\Components\TextInput::make('district')
                                        ->label('Bairro')
                                        ->maxLength(100),
                                    Forms\Components\TextInput::make('city')
                                        ->label('Cidade')
                                        ->maxLength(100),
                                    Forms\Components\TextInput::make('state')
                                        ->label('Estado')
                                        ->maxLength(2),
                                ])
                                ->columns(2),
                        ]),

                    Step::make('Contatos')
                        ->icon('heroicon-m-phone')
                        ->description('E-mail e telefone do cliente')
                        ->schema([
                            Forms\Components\Group::make()
                                ->relationship('client_contacts')
                                ->schema([
                                    Forms\Components\TextInput::make('email')
                                        ->label('E-mail')
                                        ->email()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('cellphone')
                                        ->label('Celular')
                                        ->mask('(99) 99999-9999')
                                        ->maxLength(15),
                                    Forms\Components\TextInput::make('phone')
                                        ->label('Telefone')
                                        ->mask('(99) 9999-9999')
                                        ->maxLength(14),
                                    Forms\Components\TextInput::make('optional_email')
                                        ->label('E-mail opcional')
                                        ->email()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('message_cell_phone')
                                        ->label('Celular para mensagens')
                                        ->mask('(99) 99999-9999')
                                        ->maxLength(15),
                                    Forms\Components\TextInput::make('message_phone')
                                        ->label('Telefone para mensagens')
                                        ->mask('(99) 9999-9999')
                                        ->maxLength(14),
                                    Forms\Components\Textarea::make('note')
                                        ->label('Observações')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ]),

                    Step::make('Dados Bancários')
                        ->icon('heroicon-m-currency-dollar')
                        ->description('Informações bancárias do cliente')
                        ->schema([
                            Forms\Components\Repeater::make('client_bank_accounts')
                                ->relationship('client_bank_accounts')
                                ->label('Contas Bancárias')
                                ->minItems(0)
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('bank_number')
                                                ->label('Código do banco')
                                                ->numeric()
                                                ->maxLength(3)
                                                ->placeholder('000'),
                                            Forms\Components\TextInput::make('bank_name')
                                                ->label('Nome do banco')
                                                ->required()
                                                ->maxLength(255)
                                                ->placeholder('Ex: Banco do Brasil'),
                                            Forms\Components\TextInput::make('agency')
                                                ->label('Agência')
                                                ->required()
                                                ->maxLength(20)
                                                ->placeholder('0000-0'),
                                            Forms\Components\TextInput::make('account')
                                                ->label('Conta')
                                                ->required()
                                                ->maxLength(20)
                                                ->placeholder('00000-0'),
                                        ]),
                                ])
                                ->itemLabel(
                                    fn (array $state): ?string =>
                                    $state['bank_name']
                                        ? "{$state['bank_name']} - Ag: {$state['agency']}"
                                        : 'Nova conta bancária'
                                )
                                ->addActionLabel('Adicionar conta bancária')
                                ->deleteAction(fn ($action) => $action->requiresConfirmation())
                                ->reorderable()
                                ->collapsible(),
                ]),

                    Step::make('Cônjuge')
                       ->icon('heroicon-m-user-plus')
                       ->description('Informações do cônjuge (se aplicável)')
                       ->schema([
                           Forms\Components\Hidden::make('_delete_spouse')
                               ->default(0)
                               ->dehydrated(true),

                           Forms\Components\Toggle::make('has_spouse')
                               ->label('Cliente possui cônjuge?')
                               ->live()
                               ->dehydrated(false)
                               ->afterStateHydrated(function ($component, $record) {
                                   $component->state($record?->spouse()->exists() ?? false);
                               })
                               ->afterStateUpdated(function ($state, $set) {
                                   $set('_delete_spouse', $state ? 0 : 1);
                                   if (!$state) {
                                       $set('spouse.name', null);
                                       $set('spouse.cpf', null);
                                       $set('spouse.rg', null);
                                       $set('spouse.marital_status', null);
                                       $set('spouse.father', null);
                                       $set('spouse.mother', null);
                                       $set('spouse.pis', null);
                                       $set('spouse.ctps', null);
                                       $set('spouse.profession', null);
                                       $set('spouse.date_of_birth', null);
                                       $set('spouse.place_of_birth', null);
                                       $set('spouse.nationality', null);
                                       $set('spouse.phone', null);
                                       $set('spouse.mobile', null);
                                       $set('spouse.email', null);
                                       $set('spouse.note', null);
                                   }
                               }),

                           Forms\Components\Group::make()
                               ->relationship('spouse')
                               ->schema([
                                   Forms\Components\Section::make('Dados Pessoais')
                                       ->schema([
                                           Forms\Components\TextInput::make('name')
                                               ->label('Nome completo')
                                               ->required()
                                               ->maxLength(255),
                                           Forms\Components\DatePicker::make('date_of_birth')
                                               ->label('Data de nascimento'),
                                           Forms\Components\TextInput::make('father')
                                               ->label('Pai')
                                               ->maxLength(255),
                                           Forms\Components\TextInput::make('mother')
                                               ->label('Mãe')
                                               ->maxLength(255),
                                           Forms\Components\TextInput::make('place_of_birth')
                                               ->label('Naturalidade')
                                               ->maxLength(255),
                                           Forms\Components\TextInput::make('nationality')
                                               ->label('Nacionalidade')
                                               ->maxLength(255),
                                           Forms\Components\Select::make('marital_status')
                                               ->options([
                                                   'married'  => 'Casado(a)',
                                                   'divorced' => 'Divorciado(a)',
                                                   'widowed'  => 'Viúvo(a)',
                                               ])
                                               ->label('Estado civil'),
                                           Forms\Components\TextInput::make('profession')
                                               ->label('Profissão')
                                               ->maxLength(255),
                                       ])
                                       ->columns(2),
                                   Forms\Components\Section::make('Documentos')
                                       ->schema([
                                           Forms\Components\TextInput::make('cpf')
                                               ->label('CPF')
                                               ->mask('999.999.999-99')
                                               ->maxLength(14)
                                               ->rule('cpf')
                                               ->unique(ClientSpouse::class, 'cpf', ignoreRecord: true)
                                               ->validationMessages([
                                                   'cpf'    => 'Número de CPF inválido.',
                                                   'unique' => 'Este CPF já foi registrado.',
                                               ]),
                                           Forms\Components\TextInput::make('rg')
                                               ->label('RG')
                                               ->mask('99.999.999-9')
                                               ->maxLength(12),
                                           Forms\Components\TextInput::make('pis')
                                               ->label('PIS')
                                               ->mask('999.99999.99-9')
                                               ->rule('pis')
                                               ->maxLength(14)
                                               ->validationMessages([
                                                   'pis' => 'Número de PIS inválido.',
                                               ]),
                                           Forms\Components\TextInput::make('ctps')
                                               ->label('CTPS')
                                               ->maxLength(20),
                                       ])
                                       ->columns(2),
                                   Forms\Components\Section::make('Contatos')
                                       ->schema([
                                           Forms\Components\TextInput::make('email')
                                               ->label('E-mail')
                                               ->email()
                                               ->maxLength(255),
                                           Forms\Components\TextInput::make('mobile')
                                               ->label('Celular')
                                               ->mask('(99) 99999-9999')
                                               ->maxLength(15),
                                           Forms\Components\TextInput::make('phone')
                                               ->label('Telefone')
                                               ->mask('(99) 9999-9999')
                                               ->maxLength(14),
                                           Forms\Components\Textarea::make('note')
                                               ->label('Observações')
                                               ->rows(3)
                                               ->columnSpanFull(),
                                       ])
                                       ->columns(2),
                               ])
                               ->visible(fn (callable $get) => $get('has_spouse') === true),
                       ]),

                    Step::make('Dependentes')
    ->icon('heroicon-m-user-group')
    ->description('Filhos, tutelados e curatelados (se aplicável)')
    ->schema([
        Forms\Components\Repeater::make('wards')
            ->label('Dependentes')
            ->minItems(0)
            ->defaultItems(0)
            ->afterStateHydrated(function ($component, $record) {
                if ($record) {
                    $component->state(
                        $record->wards->map(fn ($w) => [
                            'id'            => $w->id,
                            'name'          => $w->name,
                            'cpf'           => $w->cpf,
                            'rg'            => $w->rg,
                            'date_of_birth' => $w->date_of_birth?->format('Y-m-d'),
                            'note'          => $w->note,
                        ])->toArray()
                    );
                }
            })
            ->schema([
                Forms\Components\Hidden::make('id'),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome completo')
                            ->maxLength(255)
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('cpf')
                            ->label('CPF')
                            ->mask('999.999.999-99')
                            ->maxLength(14)
                            ->rule('cpf')
                            ->unique('client_wards', 'cpf', ignoreRecord: true)
                            ->validationMessages([
                                'cpf'    => 'Número de CPF inválido.',
                                'unique' => 'Este CPF já foi registrado.',
                            ]),
                        Forms\Components\TextInput::make('rg')
                            ->label('RG')
                            ->mask('99.999.999-9')
                            ->maxLength(12),
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->label('Data de nascimento')
                            ->columnSpan(2),
                        Forms\Components\Textarea::make('note')
                            ->label('Observações')
                            ->rows(2)
                            ->columnSpan(2),
                    ]),
            ])
            ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'Novo dependente')
            ->addActionLabel('Adicionar dependente')
            ->deleteAction(fn ($action) => $action->requiresConfirmation())
            ->reorderable()
            ->collapsible(),
    ]),
                ])
                    ->skippable()
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client_documents.cpf')
                    ->label('CPF'),
                Tables\Columns\TextColumn::make('client_contacts.email')
                    ->label('E-mail'),
                Tables\Columns\TextColumn::make('client_contacts.cellphone')
                    ->label('Celular'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('create_case')
                    ->label('Inserir Processo')
                    ->icon('heroicon-o-scale')
                    ->color('gray')
                    ->modalHeading(fn (Client $record) => "Novo processo — {$record->name}")
                    ->modalWidth('3xl')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('folder_number')
                                    ->label('Nº da Pasta')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('case_number')
                                    ->label('Nº do Processo')
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('court_number_id')
                                    ->label('Nº da Vara')
                                    ->options(CourtNumber::orderBy('id')->pluck('number', 'id'))
                                    ->searchable(),

                                Forms\Components\Select::make('court_name_id')
                                    ->label('Nome da Vara')
                                    ->options(CourtName::orderBy('id')->pluck('name', 'id'))
                                    ->searchable(),

                                Forms\Components\Select::make('forum_id')
                                    ->label('Fórum')
                                    ->options(Forum::orderBy('id')->pluck('name', 'id'))
                                    ->searchable(),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('lawyers')
                                    ->label('Advogado(s)')
                                    ->options(Lawyer::orderBy('name')->pluck('name', 'id'))
                                    ->multiple()
                                    ->searchable(),

                                Forms\Components\TextInput::make('opponent_name')
                                    ->label('Adverso')
                                    ->maxLength(255),

                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options(
                                        collect(CaseStatus::cases())
                                            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                                    )
                                    ->default('open')
                                    ->required(),

                                Forms\Components\Textarea::make('note')
                                    ->label('Observações')
                                    ->rows(3),
                            ]),
                    ])
                    ->action(function (Client $record, array $data) {
                        $lawyers = $data['lawyers'] ?? [];
                        unset($data['lawyers']);

                        $legalCase = $record->legalCases()->create([
                            ...$data,
                            'registered_by' => auth()->id(),
                        ]);

                        if (!empty($lawyers)) {
                            $legalCase->lawyers()->attach($lawyers);
                        }
                    })
            ->successNotificationTitle('Processo inserido com sucesso'),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                        Tables\Actions\BulkActionGroup::make([
                            Tables\Actions\DeleteBulkAction::make(),
                            Tables\Actions\ForceDeleteBulkAction::make(),
                            Tables\Actions\RestoreBulkAction::make(),
                        ]),
                    ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // ── Dados Pessoais ─────────────────────────────────────────
                Section::make('Dados Pessoais')
                    ->icon('heroicon-m-user')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nome completo')
                            ->columnSpanFull(),
                        TextEntry::make('date_of_birth')
                            ->label('Data de nascimento')
                            ->date('d/m/Y')
                            ->placeholder('—'),
                        TextEntry::make('gender')
                            ->label('Gênero')
                            ->placeholder('—')
                            ->formatStateUsing(fn ($state) => match($state) {
                                'male'   => 'Masculino',
                                'female' => 'Feminino',
                                'other'  => 'Outro',
                                default  => '—',
                            }),
                        TextEntry::make('marital_status')
                            ->label('Estado civil')
                            ->placeholder('—')
                            ->formatStateUsing(fn ($state) => match($state) {
                                'single'   => 'Solteiro(a)',
                                'married'  => 'Casado(a)',
                                'divorced' => 'Divorciado(a)',
                                'widowed'  => 'Viúvo(a)',
                                'separated' => 'Separado(a)',
                                default    => '—',
                            }),
                        TextEntry::make('profession')
                            ->label('Profissão')
                            ->placeholder('—'),
                        TextEntry::make('nationality')
                            ->label('Nacionalidade')
                            ->placeholder('—'),
                        TextEntry::make('place_of_birth')
                            ->label('Naturalidade')
                            ->placeholder('—'),
                        TextEntry::make('father')
                            ->label('Pai')
                            ->placeholder('—'),
                        TextEntry::make('mother')
                            ->label('Mãe')
                            ->placeholder('—'),
                        TextEntry::make('note')
                            ->label('Observações')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),

                // ── Documentos ─────────────────────────────────────────────
                Section::make('Documentos')
                    ->icon('heroicon-m-document-text')
                    ->columns(3)
                    ->relationship('client_documents')
                    ->schema([
                    TextEntry::make('cpf')
                            ->label('CPF')
                            ->placeholder('—'),
                    TextEntry::make('rg')
                            ->label('RG')
                            ->placeholder('—'),
                    TextEntry::make('cnh')
                            ->label('CNH')
                            ->placeholder('—'),
                    TextEntry::make('pis')
                            ->label('PIS')
                            ->placeholder('—'),
                    TextEntry::make('ctps')
                            ->label('CTPS')
                            ->placeholder('—'),
                    TextEntry::make('rnm')
                            ->label('RNM')
                            ->placeholder('—'),
                    TextEntry::make('other_documents')
                            ->label('Outros Documentos')
                            ->placeholder('—')
                            ->columnSpanFull(),
                ]),

                // ── Endereço ───────────────────────────────────────────────
                Section::make('Endereço')
                    ->icon('heroicon-m-map-pin')
                    ->columns(3)
                    ->relationship('client_addresses')
                    ->schema([
                    TextEntry::make('zipcode')
                            ->label('CEP')
                            ->placeholder('—'),
                    TextEntry::make('street')
                            ->label('Logradouro')
                            ->placeholder('—'),
                    TextEntry::make('number')
                            ->label('Número')
                            ->placeholder('—'),
                    TextEntry::make('complement')
                            ->label('Complemento')
                            ->placeholder('—'),
                    TextEntry::make('district')
                            ->label('Bairro')
                            ->placeholder('—'),
                    TextEntry::make('city')
                            ->label('Cidade')
                            ->placeholder('—'),
                    TextEntry::make('state')
                            ->label('Estado')
                            ->placeholder('—'),
                ]),

                // ── Contatos ───────────────────────────────────────────────
                Section::make('Contatos')
                    ->icon('heroicon-m-phone')
                    ->columns(2)
                    ->relationship('client_contacts')
                    ->schema([
                    TextEntry::make('email')
                            ->label('E-mail')
                            ->placeholder('—')
                            ->url(fn ($state) => $state ? "mailto:{$state}" : null),
                    TextEntry::make('optional_email')
                            ->label('E-mail Alternativo')
                            ->placeholder('—')
                            ->url(fn ($state) => $state ? "mailto:{$state}" : null),
                    TextEntry::make('cellphone')
                            ->label('Celular')
                            ->placeholder('—'),
                    TextEntry::make('phone')
                            ->label('Telefone Fixo')
                            ->placeholder('—'),
                    TextEntry::make('message_cell_phone')
                            ->label('WhatsApp / Celular Recado')
                            ->placeholder('—'),
                    TextEntry::make('message_phone')
                            ->label('Telefone Recado')
                            ->placeholder('—'),
                    TextEntry::make('note')
                            ->label('Observações')
                            ->placeholder('—')
                            ->columnSpanFull(),
                ]),

                // ── Dados Bancários ────────────────────────────────────────
                Section::make('Dados Bancários')
                    ->icon('heroicon-m-banknotes')
                    ->schema([
                    RepeatableEntry::make('client_bank_accounts')
                            ->label('')
                            ->columns(4)
                            ->schema([
                                TextEntry::make('bank_number')
                                    ->label('Número do Banco')
                                    ->placeholder('—'),
                                TextEntry::make('bank_name')
                                    ->label('Nome do Banco')
                                    ->placeholder('—'),
                                TextEntry::make('agency')
                                    ->label('Agência')
                                    ->placeholder('—'),
                                TextEntry::make('account')
                                    ->label('Conta')
                                    ->placeholder('—'),
                            ]),
                ]),

                // ── Cônjuge ────────────────────────────────────────────────
                Section::make('Cônjuge')
                    ->icon('heroicon-m-user-plus')
                    ->columns(3)
                    ->relationship('spouse')
                    ->hidden(fn ($record) => !$record?->spouse()->exists())
                    ->schema([
                    TextEntry::make('name')
                            ->label('Nome completo')
                            ->columnSpanFull()
                            ->placeholder('—'),
                    TextEntry::make('date_of_birth')
                            ->label('Data de nascimento')
                            ->date('d/m/Y')
                            ->placeholder('—'),
                    TextEntry::make('marital_status')
                            ->label('Estado civil')
                            ->placeholder('—')
                            ->formatStateUsing(fn ($state) => match($state) {
                                'married'  => 'Casado(a)',
                                'divorced' => 'Divorciado(a)',
                                'widowed'  => 'Viúvo(a)',
                                default    => '—',
                            }),
                    TextEntry::make('profession')
                            ->label('Profissão')
                            ->placeholder('—'),
                    TextEntry::make('nationality')
                            ->label('Nacionalidade')
                            ->placeholder('—'),
                    TextEntry::make('place_of_birth')
                            ->label('Naturalidade')
                            ->placeholder('—'),
                    TextEntry::make('father')
                            ->label('Pai')
                            ->placeholder('—'),
                    TextEntry::make('mother')
                            ->label('Mãe')
                            ->placeholder('—'),
                    TextEntry::make('email')
                            ->label('E-mail')
                            ->placeholder('—')
                            ->url(fn ($state) => $state ? "mailto:{$state}" : null),
                    TextEntry::make('mobile')
                            ->label('Celular')
                            ->placeholder('—'),
                    TextEntry::make('phone')
                            ->label('Telefone')
                            ->placeholder('—'),
                    TextEntry::make('cpf')
                            ->label('CPF')
                            ->placeholder('—'),
                    TextEntry::make('rg')
                            ->label('RG')
                            ->placeholder('—'),
                    TextEntry::make('pis')
                            ->label('PIS')
                            ->placeholder('—'),
                    TextEntry::make('ctps')
                            ->label('CTPS')
                            ->placeholder('—'),
                    TextEntry::make('note')
                            ->label('Observações')
                            ->placeholder('—')
                            ->columnSpanFull(),
                ]),

                // ── Dependentes ────────────────────────────────────────────
                Section::make('Dependentes')
                    ->icon('heroicon-m-users')
                    ->hidden(fn ($record) => $record?->wards()->doesntExist())
                    ->schema([
                    RepeatableEntry::make('wards')
                            ->label('')
                            ->columns(3)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nome'),
                                TextEntry::make('date_of_birth')
                                    ->label('Data de nascimento')
                                    ->date('d/m/Y')
                                    ->placeholder('—'),
                                TextEntry::make('cpf')
                                    ->label('CPF')
                                    ->placeholder('—'),
                                TextEntry::make('rg')
                                    ->label('RG')
                                    ->placeholder('—'),
                                TextEntry::make('note')
                                    ->label('Observações')
                                    ->placeholder('—')
                                    ->columnSpanFull(),
                            ]),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'view' => Pages\ViewClient::route('/{record}'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
