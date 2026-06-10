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
- **Prazos** (`DeadlineResource`): módulo separado das Tarefas por implicação de responsabilidade civil (prazo fatal perdido). Vinculado **obrigatoriamente** a um processo. Campos: processo, tipo de prazo (enum `DeadlineType`), prazo fatal (obrigatório), prazo interno (opcional, validado `before_or_equal:fatal_date`), advogado(s) responsáveis (sugeridos automaticamente a partir do pivot do processo ao selecioná-lo), status (enum `DeadlineStatus`), observações. Coluna calculada "Dias restantes" com cor por proximidade (danger ≤3 dias/vencido, warning ≤7). Ação rápida "Cumprir". Data fatal digitada manualmente (sem cálculo automático — complexidade de feriados). Espelha o padrão `HearingResource`.
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
- Calendário exibe prazos como eventos de dia inteiro: dois por prazo — fatal (vermelho) e interno (laranja, só se preenchido). Prazo não pode ser excluído pelo popover do calendário (botão Excluir oculto para `type === 'deadline'`); exclusão apenas pelo Resource com soft delete.
- Calendário integrado com o Google Calendar de forma bidirecional
- Criado lixeira dos soft-deletes com plugin Revive, somente super-admin e admin tem permissão, utilizado Custom page para tradução ao pt-br
- Registros de atividades dos usuários com plugin ActivityLog - spatie/laravel-activitylog, somente super-admin e admin tem permissão, utilizado Custom page para tradução ao pt-br

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

### Módulo de Prazos — detalhes técnicos

- **Enums:** `DeadlineType` (Contestação, Recurso/Apelação, Manifestação, Embargos de Declaração, Cumprimento de Sentença, Réplica, Contrarrazões, Agravo de Instrumento, Alegações Finais, Emenda à Inicial, Outro) e `DeadlineStatus` (Pendente, Cumprido, Perdido, Cancelado). Tipos como enum (não tabela) por opção de simplicidade; `Outro` como coringa.
- **Tabelas:** `deadlines`, pivot `deadline_lawyer` (múltiplos advogados).
- **Sincronização Google:** cada prazo gera **dois** eventos (fatal + interno), ambos dia inteiro. Por isso a pivot `deadline_google_events` tem coluna extra `date_type` ('fatal'/'internal') e unique em `(deadline_id, user_id, date_type)` — diferente de `hearing_google_events`/`task_google_events` que são 1:1 por token.
- **`DeadlineObserver`:** create/update passam pelo mesmo `syncDeadlineEventsForUser()` no Service (usa `insert` se não há pivot, `update` se há), cobrindo o caso "usuário conectou depois". Se a data interna for removida na edição, o evento interno e sua pivot são apagados. `restored()` recria os eventos.
- **All-day no Google:** `end.date` é exclusivo, então o `end` usa `buildDateTime($date, null, 1)` (+1 dia) para o evento ocupar exatamente 1 dia.
- Lembretes: fatal popup 1 dia antes + email 2 dias antes; interno popup 1 dia antes.

## Convenções adotadas

- Nomes de tabelas e campos em inglês, padrão Laravel
- Exceção: termos processuais brasileiros sem tradução útil ficam em português nos *cases* do enum sem acento (ex: `DeadlineType::EmbargosDeclaracao`); classe, tabela e coluna seguem inglês
- Soft deletes em todos os models principais
- Enums PHP 8.1 com `label()` e `color()` para tradução e badge
- `BadgeColumn` para exibição de status
- Localização concatenada via `collect()->filter()->join(' - ')`
- Model de processo: `LegalCase` (evita palavra reservada `Case`)
- FKs longas nomeadas manualmente para respeitar limite de 64 chars do MySQL
- Templates PF e PJ sempre em tabelas separadas para evitar contaminação de modelos

## Testes e correções

- [ ] Alterar a forma como os documentos são armazenados do BD e como são exibidos no RelationManager

## O que falta construir

- [ ] Dashboard customizada (somente no final)
