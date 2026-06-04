<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnterpriseResource\Pages;
use App\Models\Enterprise;
use App\Models\EnterpriseDocument;
use App\Models\CourtName;
use App\Models\CourtNumber;
use App\Models\Forum;
use App\Models\Lawyer;
use App\Enums\CaseStatus;
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
use Leandrocfe\FilamentPtbrFormFields\Cep;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EnterpriseResource extends Resource
{
    protected static ?string $model = Enterprise::class;
    protected static ?string $modelLabel = 'Empresa';
    protected static ?string $navigationGroup = 'Controle de Clientes';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Pessoa Jurídica';
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\EnterpriseResource\RelationManagers\LegalCasesRelationManager::class,
            \App\Filament\Resources\EnterpriseResource\RelationManagers\EnterprisePowersOfAttorneyRelationManager::class,
            \App\Filament\Resources\EnterpriseResource\RelationManagers\EnterpriseFeeAgreementsRelationManager::class,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Dados da Empresa')
                        ->icon('heroicon-m-building-office')
                        ->schema([
                            Forms\Components\TextInput::make('corporate_reason')
                                ->label('Razão Social')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('trade_name')
                                ->label('Nome Fantasia')
                                ->maxLength(255),
                            Forms\Components\DatePicker::make('opening_date')
                                ->label('Data de Abertura'),
                            Forms\Components\Textarea::make('note')
                                ->label('Observações')
                                ->rows(3)
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

                    Step::make('Documentos')
                        ->icon('heroicon-m-document-text')
                        ->schema([
                            Forms\Components\Group::make()
                                ->relationship('enterprise_documents')
                                ->schema([
                                    Forms\Components\TextInput::make('cnpj')
                                        ->label('CNPJ')
                                        ->required()
                                        ->mask('99.999.999/9999-99')
                                        ->rule('cnpj')
                                        ->maxLength(18)
                                        ->unique(EnterpriseDocument::class, 'cnpj', ignoreRecord: true)
                                        ->validationMessages([
                                            'required' => 'O campo CNPJ é obrigatório.',
                                            'cnpj'     => 'Número de CNPJ inválido.',
                                            'unique'   => 'Este CNPJ já foi registrado.',
                                        ]),
                                    Forms\Components\TextInput::make('ie')
                                        ->label('Inscrição Estadual')
                                        ->maxLength(18),
                                    Forms\Components\TextInput::make('im')
                                        ->label('Inscrição Municipal')
                                        ->maxLength(18),
                                    Forms\Components\Textarea::make('other_documents')
                                        ->label('Outros Documentos')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ]),

                    Step::make('Endereço')
                        ->icon('heroicon-m-map-pin')
                        ->schema([
                            Forms\Components\Group::make()
                                ->relationship('enterprise_addresses')
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
                        ->schema([
                            Forms\Components\Group::make()
                                ->relationship('enterprise_contacts')
                                ->schema([
                                    Forms\Components\TextInput::make('email')
                                        ->label('E-mail')
                                        ->email()
                                        ->rule('email')
                                        ->maxLength(50),
                                    Forms\Components\TextInput::make('optional_email')
                                        ->label('E-mail Alternativo')
                                        ->email()
                                        ->rule('email')
                                        ->maxLength(50),
                                    Forms\Components\TextInput::make('cellphone')
                                        ->label('Celular')
                                        ->mask('(99) 99999-9999')
                                        ->maxLength(20)
                                        ->formatStateUsing(fn ($state) => blank($state) ? '' : $state)
                                         ->dehydrateStateUsing(
                                             fn ($state) => filled(preg_replace('/\D/', '', $state ?? ''))
                                                 ? $state
                                                 : null
                                         ),
                                    Forms\Components\TextInput::make('phone')
                                        ->label('Telefone Fixo')
                                        ->mask('(99) 9999-9999')
                                        ->maxLength(20)
                                        ->formatStateUsing(fn ($state) => blank($state) ? '' : $state)
                                         ->dehydrateStateUsing(
                                             fn ($state) => filled(preg_replace('/\D/', '', $state ?? ''))
                                                 ? $state
                                                 : null
                                         ),
                                    Forms\Components\TextInput::make('message_cell_phone')
                                        ->label('WhatsApp / Celular Recado')
                                        ->mask('(99) 99999-9999')
                                        ->maxLength(20)
                                        ->formatStateUsing(fn ($state) => blank($state) ? '' : $state)
                                         ->dehydrateStateUsing(
                                             fn ($state) => filled(preg_replace('/\D/', '', $state ?? ''))
                                                 ? $state
                                                 : null
                                         ),
                                    Forms\Components\TextInput::make('message_phone')
                                        ->label('Telefone Recado')
                                        ->mask('(99) 9999-9999')
                                        ->maxLength(20)
                                        ->formatStateUsing(fn ($state) => blank($state) ? '' : $state)
                                         ->dehydrateStateUsing(
                                             fn ($state) => filled(preg_replace('/\D/', '', $state ?? ''))
                                                 ? $state
                                                 : null
                                         ),
                                    Forms\Components\Textarea::make('note')
                                        ->label('Observações')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ]),

                    Step::make('Dados Bancários')
                        ->icon('heroicon-m-banknotes')
                        ->schema([
                            Forms\Components\Repeater::make('enterprise_bank_accounts')
                                ->relationship('enterprise_bank_accounts')
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
                                                ->maxLength(255)
                                                ->placeholder('Ex: Banco do Brasil'),
                                            Forms\Components\TextInput::make('agency')
                                                ->label('Agência')
                                                ->extraInputAttributes([
                                                'oninput' => "this.value = this.value.replace(/[^0-9\-]/g, '')",
                                            ])
                                                ->maxLength(20)
                                                ->placeholder('0000-0'),

                                            Forms\Components\TextInput::make('account')
                                                ->label('Conta')
                                                ->extraInputAttributes([
                                                'oninput' => "this.value = this.value.replace(/[^0-9\-]/g, '')",
                                            ])
                                                ->maxLength(20)
                                                ->placeholder('00000-0'),
                                        ])
                                        ->columns(4),
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

                    Step::make('Representantes')
                        ->icon('heroicon-m-users')
                        ->schema([
                            Forms\Components\Repeater::make('enterprise_representatives')
                                ->relationship()
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Nome')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\Select::make('gender')
                                        ->label('Gênero')
                                        ->options([
                                            'male'   => 'Masculino',
                                            'female' => 'Feminino',
                                            'other'  => 'Outro',
                                        ]),
                                    Forms\Components\TextInput::make('position')
                                        ->label('Cargo / Função')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('cpf')
                                        ->label('CPF')
                                        ->mask('999.999.999-99')
                                        ->maxLength(14),
                                    Forms\Components\TextInput::make('rg')
                                        ->label('RG')
                                        ->mask('99.999.999-9')
                                        ->maxLength(12),
                                    Forms\Components\TextInput::make('email')
                                        ->label('E-mail')
                                        ->email()
                                        ->rule('email')
                                        ->maxLength(100),
                                    Forms\Components\TextInput::make('phone')
                                        ->label('Telefone / Celular')
                                        ->mask('(99) 99999-9999')
                                        ->maxLength(20)
                                        ->extraInputAttributes([
        'x-effect' => '$wire; if (!/\d/.test($el.value)) $el.value = ""',
    ])
    ->dehydrateStateUsing(fn ($state) => filled(preg_replace('/[^0-9]/', '', $state ?? '')) ? $state : null),
                                    Forms\Components\Textarea::make('note')
                                        ->label('Observações')
                                        ->rows(2)
                                        ->columnSpanFull(),
                                ])
                                ->columns(2)
                                ->addActionLabel('Adicionar Representante')
                                ->deleteAction(fn ($action) => $action->requiresConfirmation())
                                ->collapsible()
                                ->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull()
                ->skippable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\TextColumn::make('corporate_reason')
                        ->label('Razão Social')
                        ->searchable()
                        ->sortable()
                        ->weight(\Filament\Support\Enums\FontWeight::Bold),

                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('enterprise_documents.cnpj')
                            ->label('CNPJ')
                            ->icon('heroicon-m-building-office'),
                    ])->space(1),

                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('enterprise_contacts.cellphone')
                            ->label('Celular')
                            ->icon('heroicon-m-phone')
                            ->placeholder('—'),
                        Tables\Columns\TextColumn::make('enterprise_contacts.email')
                            ->label('E-mail')
                            ->icon('heroicon-m-envelope')
                            ->placeholder('—'),
                    ])->space(1),
                ]),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
                // ── Dados da Empresa ───────────────────────────────────────
                Section::make('Dados da Empresa')
                    ->icon('heroicon-m-building-office')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('corporate_reason')
                            ->label('Razão Social'),
                        TextEntry::make('trade_name')
                            ->label('Nome Fantasia')
                            ->placeholder('—'),
                        TextEntry::make('opening_date')
                            ->label('Data de Abertura')
                            ->placeholder('—'),
                        TextEntry::make('note')
                            ->label('Observações')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),

                // ── Documentos ─────────────────────────────────────────────
                Section::make('Documentos')
                    ->icon('heroicon-m-document-text')
                    ->columns(2)
                    ->relationship('enterprise_documents')
                    ->schema([
                        TextEntry::make('cnpj')
                            ->label('CNPJ')
                            ->placeholder('—'),
                        TextEntry::make('ie')
                            ->label('Inscrição Estadual')
                            ->placeholder('—'),
                        TextEntry::make('im')
                            ->label('Inscrição Municipal')
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
                    ->relationship('enterprise_addresses')
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
                    ->relationship('enterprise_contacts')
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
                    ->columns(2)
                    ->relationship('enterprise_bank_accounts')
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

                // ── Representantes ─────────────────────────────────────────
                Section::make('Representantes')
                    ->icon('heroicon-m-users')
                    ->schema([
                        RepeatableEntry::make('enterprise_representatives')
                            ->label('')
                            ->columns(3)
                            ->schema([
                                TextEntry::make('name')->label('Nome'),
                                TextEntry::make('gender')
                                    ->label('Gênero')
                                    ->formatStateUsing(fn ($state) => match($state) {
                                        'male'   => 'Masculino',
                                        'female' => 'Feminino',
                                        'other'  => 'Outro',
                                        default  => '—',
                                    }),
                                TextEntry::make('position')->label('Cargo / Função')->placeholder('—'),
                                TextEntry::make('cpf')->label('CPF')->placeholder('—'),
                                TextEntry::make('rg')->label('RG')->placeholder('—'),
                                TextEntry::make('email')->label('E-mail')->placeholder('—')
                                    ->url(fn ($state) => $state ? "mailto:{$state}" : null),
                                TextEntry::make('phone')->label('Telefone / Celular')->placeholder('—'),
                                TextEntry::make('note')->label('Observações')->placeholder('—')->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEnterprises::route('/'),
            'create' => Pages\CreateEnterprise::route('/create'),
            'view'   => Pages\ViewEnterprise::route('/{record}'),
            'edit'   => Pages\EditEnterprise::route('/{record}/edit'),
        ];
    }
}
