#!/usr/bin/env bash
#
# deploy.sh — Atualiza o LexFirma em produção a partir do git.
#
# Uso na VPS (a partir de qualquer diretório):
#   bash /home/lexfirma/web/lex.labjuns.com.br/private/app_source/deploy.sh
#
# Requisitos: usuário com sudo (ex.: dev). Roda os comandos do Laravel
# como o usuário 'lexfirma' (dono dos arquivos do site).
#
# O que faz, em ordem:
#   1. Puxa o código novo do git (branch main)
#   2. Reinstala dependências de produção (só se composer.lock mudou)
#   3. Roda migrations pendentes
#   4. Recompila os caches de produção (config, rotas, views, Filament)
#   5. Reinicia o worker de filas para carregar o código novo
#
set -euo pipefail

# ── Configuração ──────────────────────────────────────────────
APP="/home/lexfirma/web/lex.labjuns.com.br/private/app_source"
USER="lexfirma"
BRANCH="main"
PHP="/usr/bin/php"
# ──────────────────────────────────────────────────────────────

run() { sudo -u "$USER" "$@"; }

echo "==> Deploy LexFirma iniciado ($(date '+%Y-%m-%d %H:%M:%S'))"
cd "$APP"

# 1. Código
echo "==> git pull ($BRANCH)"
LOCK_BEFORE="$(md5sum composer.lock 2>/dev/null | awk '{print $1}')"
run git pull origin "$BRANCH"
LOCK_AFTER="$(md5sum composer.lock 2>/dev/null | awk '{print $1}')"

# 2. Dependências — só reinstala se o composer.lock mudou (economiza tempo)
if [ "$LOCK_BEFORE" != "$LOCK_AFTER" ]; then
    echo "==> composer.lock mudou — reinstalando dependências"
    run composer install --no-dev --optimize-autoloader --no-interaction
else
    echo "==> composer.lock inalterado — pulando composer install"
fi

# 3. Migrations (--force: obrigatório em produção)
echo "==> migrate --force"
run "$PHP" artisan migrate --force

# 4. Caches de produção
echo "==> recompilando caches"
run "$PHP" artisan config:cache
run "$PHP" artisan route:cache
run "$PHP" artisan view:cache
run "$PHP" artisan filament:cache-components

# 5. Reinicia o worker de filas (carrega o código novo)
echo "==> queue:restart"
run "$PHP" artisan queue:restart

echo "==> Deploy concluído com sucesso ($(date '+%Y-%m-%d %H:%M:%S'))"
