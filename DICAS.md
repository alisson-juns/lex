# php artisan key:generate

-   [Traduzir para o português](https://github.com/lucascudo/laravel-pt-BR-localization):

## php artisan lang:publish

## composer require lucascudo/laravel-pt-br-localization --dev

## php artisan vendor:publish --tag=laravel-pt-br-localization

-   **[Altere Linha 85 do arquivo config/app.php para: 'locale' => 'pt_BR' ]**
-   **[Para versões 11.x altere a linha 8 do arquivo .env APP_LOCALE=pt_BR ]**

\\ ---------------------------------------------------------------------- \\

\\ Remover pacotes do Laravel \\

Remove declaration from file composer.json (in the "require" section)
Remove any class aliases from file app.php
Remove any references to the package from my code :-)
Digite: composer update
Digite: composer dump-autoload

\\------------------------------------------------------------------------\\

-   [Criar uma rota de logout customizada usando Filament](https://filamentphp.com/content/tim-wassenburg-how-to-customize-logout-redirect)

\\------------------------------------------------------------------------\\

\\ Limpar cache laravel \\
php artisan config:clear

php artisan route:clear

php artisan view:clear

php artisan cache:clear

php artisan serve

\\-----------------------------------------------------------------------\\

QUEUE_CONNECTION=sync # alterar database para synç para enviar email

Plugins
https://filamentphp.com/plugins/leandrocfe-brazilian-form-fields
https://filamentphp.com/plugins/diogogpinto-auth-ui-enhancer

\\-----------------------------------------------------------------------\\
//Mudar a rota

Acrestar path('') no AdminPanelProvider.php e retirar o caminho no routes/web.php

\\-----------------------------------------------------------------------\\
//Mostrar os dados do usuário

Acrescentar ->profile(isSimple: false) no AdminPanelProvider.php

\\-----------------------------------------------------------------------\\
//Cria o usuário inicial
php artisan make:filament-user

\\-----------------------------------------------------------------------\\
//Criar os views

php artisan make:filament-resource NOME_DO_RESOURCE --generate --view (VAI CRIAR AS TABELAS e os views NO FILAMENT)

\\----------------------------------------------------------------------\\

//Criar migração e model

php artisan make:model Battalion -m

//Criar só migração

php artisan make:migration add_approved_at_approved_by_to_militaries

\\----------------------------------------------------------------------\\

//Fake Filler - extensão para autopreencher formulários

//Nos Resoucers incluir em action Tables\Actions\DeleteAction::make() botão excluir na tabela

\\----------------------------------------------------------------------\\

Publicar no Git

echo "# sgm" >> README.md
git init
git add README.md
git commit -m "first commit"
git branch -M main
git remote add origin https://github.com/alisson-juns/sgm.git (Alterar o endereço remoto)
git push -u origin main

\\ ------------- CRIAR SEEDERS ----------------- \\

php artisan make:seeder CommandSeeder
php artisan make:seeder BattalionSeeder
php artisan make:seeder CompanySeeder
php artisan make:seeder SectionSeeder

--Rodar 1 por 1--
php artisan db:seed --class=CommandSeeder
php artisan db:seed --class=BattalionSeeder
php artisan db:seed --class=CompanySeeder
php artisan db:seed --class=SectionSeeder
php artisan db:seed --class=NaturezaSeeder
php artisan db:seed --class=UnidadeSeeder

\\-------------- Tinker ----------------------- \\
php artisan tinker
Schema::getColumnListing('your_table_name_here');

\\-------------- Criar diretórios --------------\\

New-Item -ItemType Directory -Path "app/Mail" -Force
New-Item -ItemType File -Path "app/Mail/MilitaryRegistrationApproved.php" -Force

\\-------------- Alias Artisan ----------------\\

Abre o PowerShell como administrador
1 - Digite: notepad $PROFILE
2 - Escreva: function art { php artisan $args } 
3 - Salve e feche o Notepad
4 - Digite: . $PROFILE


