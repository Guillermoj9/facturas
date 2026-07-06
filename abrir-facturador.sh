#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

if docker compose version >/dev/null 2>&1; then
    compose=(docker compose)
elif command -v docker-compose >/dev/null 2>&1; then
    compose=(docker-compose)
else
    printf 'No encuentro Docker Compose. Instala Docker Desktop o docker-compose.\n' >&2
    exit 1
fi

"${compose[@]}" up -d --build

url="http://localhost:8000"

if command -v xdg-open >/dev/null 2>&1; then
    xdg-open "$url" >/dev/null 2>&1 &
else
    printf 'Facturador listo: %s\n' "$url"
fi
