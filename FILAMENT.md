**[Para instalar]**

# composer require filament/filament
# php artisan filament:install --panels
# php artisan make:filament-user

\\---------------------------\\

\\ No AdminPanelProvider
->path('') **[Para iniciar na tela de login ]**
->profile(isSimple: false) **[Para aparecer os dados do usuário ]**
->brandName('SGM - Controle de Estoque') **[Para alterar o nome da aplicação ]**


**[Para alterar o tema ]**
->colors([
                'danger' => Color::Red,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'primary' => Color::Slate,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
        ])

\\-----------------------------\\
**[Criar as views]**

php artisan make:filament-resource NOME_DO_RESOURCE --generate --view (VAI CRIAR AS TABELAS e os views NO FILAMENT)

\\-----------------------------\\
**[Customizar os Resources]**
protected static ?string $modelLabel = 'Usuários';

protected static ?string $navigationLabel = 'Usuários';

protected static ?string $navigationGroup = 'Controle de Usuários';