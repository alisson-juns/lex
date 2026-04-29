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
- **Clientes — Pessoa Jurídica** (`EnterpriseResource`): cadastro com Wizard (dados da empresa, documentos, representantes).
- **Processos** (`LegalCaseResource`): vinculado a pessoa física ou jurídica via Radio. Campos: nº pasta, nº processo, localização (vara + fórum), advogado, adverso, status, observações.
- **Audiências** (`HearingResource`): vinculada obrigatoriamente a um processo. Campos: descrição, data, hora, local, advogado, status, observações.
- **Usuários**: gerenciado pelo Filament nativo + Shield.

### Funcionalidades transversais
- Inserir processo direto da listagem de clientes e empresas (Action com modal)
- Aba de processos na view do cliente e da empresa (RelationManager)
- Soft delete em todos os módulos principais
- Enums com método `label()` em português para status

### Estrutura de localização do processo
Três tabelas independentes sem FK entre si:
- `forums` → nome do fórum (419 registros, dados do TJSP)
- `court_names` → nome da vara (ex: Vara Cível, Vara Criminal)
- `court_numbers` → número da vara (1ª a 99ª)

Decisão: tabelas separadas para manter padrão de preenchimento via dropdown, sem obrigar combinações predefinidas. O processo salva `forum_id`, `court_name_id` e `court_number_id` como FKs independentes e nullable.

## Convenções adotadas
- Nomes de tabelas e campos em inglês, padrão Laravel
- Soft deletes em todos os models principais
- Enums PHP 8.1 com `label()` para tradução
- `BadgeColumn` para exibição de status
- Localização concatenada via `collect()->filter()->join(' - ')`
- Model de processo: `LegalCase` (evita palavra reservada `Case`)

## O que falta construir
- [ ] Geração de documentos e procurações (PDF)
- [ ] FullCalendar integrado com Google Agenda
- [ ] Dashboard customizada
- [ ] Módulo de advogados
- [ ] Módulo de estagiários
- [ ] Módulo de funcionários