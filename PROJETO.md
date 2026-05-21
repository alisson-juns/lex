# LexFirma — Documentação do Projeto

## Objetivo

Sistema de gestão para escritório de advocacia. Controle de processos, clientes, audiências, advogados, estagiários e funcionários.

## Usuários

Advogados (acesso inicial). Controle de permissões via Filament Shield.

## Stack

- Laravel 12 + Filament 3.3
- PHP 8.3
- MariaDB (VPS Hetzner, Ubuntu 24.04, HestiaCP)
- Filament Shield para roles e permissões

## O que já está pronto

### Módulos

- **Clientes — Pessoa Física** (`ClientResource`): cadastro completo com Wizard (dados pessoais, documentos, endereço, contatos, dados bancários, cônjuge, dependentes). CEP automático via ViaCEP.
- **Clientes — Pessoa Jurídica** (`EnterpriseResource`): cadastro com Wizard (dados da empresa, documentos, endereço, contatos, dados bancários, representantes).
- **Processos** (`LegalCaseResource`): vinculado a pessoa física ou jurídica. Campos: nº pasta, nº processo, localização (vara + fórum), advogados (múltiplos via pivot), adverso, status, observações.
- **Audiências** (`HearingResource`): vinculada obrigatoriamente a um processo. Campos: descrição, data, hora, local, advogado, status, observações.
- **Advogados** (`LawyerResource`): cadastro completo com dados pessoais, OAB, documentos, endereço, contatos. Vinculado opcionalmente a um usuário do sistema.
- **Funcionários** (`EmployeeResource`): cadastro com dados pessoais, documentos, endereço, contatos e cargo (Occupation).
- **Tarefas** (`TaskResource`): vinculada opcionalmente a um processo. Campos: título, descrição, prazo, hora, advogado(s) responsáveis, status.
- **Usuários**: gerenciado pelo Filament nativo + Shield.

### Documentos — Pessoa Física

- **Procurações PF** (`PowerOfAttorneyResource`): geração via modal na view do cliente. Template editável com TinyMCE e placeholders de dados do cliente e advogados. PDF gerado via DomPDF.
- **Contratos de Honorários PF** (`FeeAgreementResource`): geração via modal na view do cliente. Campos: tipo de ação, percentual de honorários, advogado(s). Template separado do PJ.
- **Declaração de Gratuidade** (`GratuityDeclarationResource`): geração via modal na view do cliente. Template editável. PDF sem identidade visual do escritório.

### Documentos — Pessoa Jurídica

- **Procurações PJ** (`EnterprisePowerOfAttorneyResource`): geração via modal na view da empresa. Inclui seleção de representante legal e advogado(s). Template editável com placeholders de dados da empresa e representante.
- **Contratos de Honorários PJ** (`EnterpriseFeeAgreementResource`): geração via modal na view da empresa. Template separado do PF com placeholders de CNPJ, razão social e representante.

### Templates de documentos (Configurações)

- `PowerOfAttorneyTemplateResource` — modelos de procuração PF
- `EnterprisePowerOfAttorneyTemplateResource` — modelos de procuração PJ
- `FeeAgreementTemplateResource` — modelos de contrato PF
- `EnterpriseFeeAgreementTemplateResource` — modelos de contrato PJ
- `GratuityDeclarationTemplateResource` — modelos de declaração de gratuidade

### Funcionalidades transversais

- Geração de documentos via modal na view do cliente/empresa → redireciona para edição com TinyMCE → botão Gerar PDF
- Aba de processos na view do cliente e da empresa (RelationManager)
- Aba de documentos (procurações, contratos, declarações) na view do cliente e da empresa
- Inserir processo direto da listagem de clientes e empresas (Action com modal)
- Soft delete em todos os módulos principais
- Enums com método `label()` em português para status
- Configurações do escritório (`FirmSettings`): nome, endereço, logo, posição do logo, cidades/estados para feriados
- Calendário (`CalendarPage`) com widget de audiências e tarefas próximas
- Calendário integrado com o Google Calendar de forma bidirecional
- Criado lixeira dos soft-deletes com plugin Revive, somente super-admin e admin tem permissão, utilizado Custom page para tradução ao pt-br

### Estrutura de localização do processo

Três tabelas independentes sem FK entre si:

- `forums` → nome do fórum (419 registros, dados do TJSP)
- `court_names` → nome da vara (ex: Vara Cível, Vara Criminal)
- `court_numbers` → número da vara (1ª a 99ª)

Decisão: tabelas separadas para manter padrão de preenchimento via dropdown, sem obrigar combinações predefinidas. O processo salva `forum_id`, `court_name_id` e `court_number_id` como FKs independentes e nullable.

### Padrão de geração de documentos

Todos os documentos seguem o mesmo fluxo:

1. Modal na view do cliente/empresa coleta os dados necessários
2. `Service::render()` substitui placeholders no `body_text` do template (chips `<span data-placeholder>` e `{{key}}`)
3. `rendered_body` salvo no registro e aberto no TinyMCE para edição final
4. `Service::generate()` gera o PDF via DomPDF e salva em `Storage::disk('public')`
5. `pdf_path` salvo no registro para acesso direto sem regenerar

## Convenções adotadas

- Nomes de tabelas e campos em inglês, padrão Laravel
- Soft deletes em todos os models principais
- Enums PHP 8.1 com `label()` e `color()` para tradução e badge
- `BadgeColumn` para exibição de status
- Localização concatenada via `collect()->filter()->join(' - ')`
- Model de processo: `LegalCase` (evita palavra reservada `Case`)
- FKs longas nomeadas manualmente para respeitar limite de 64 chars do MySQL
- Templates PF e PJ sempre em tabelas separadas para evitar contaminação de modelos

## O que falta construir

- [ ] Dashboard customizada utilizar plugin Nord Theme
- [ ] Login customizado com o plugin Filament Breezy
- [ ] Registros de atividades dos usuários com plugin ActivityLog - spatie/laravel-activitylog

## Extras não prioritários

- [ ] Filament Spatie Google Fonts
- [ ] Plugin Peek - full-screen preview modal
- [ ] Plugin Curator - media manager
