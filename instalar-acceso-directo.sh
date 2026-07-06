#!/usr/bin/env bash
set -euo pipefail

project_dir="$(cd "$(dirname "$0")" && pwd)"
desktop_dir="$(xdg-user-dir DESKTOP 2>/dev/null || printf '%s/Desktop' "$HOME")"

mkdir -p "$desktop_dir"

cat > "$desktop_dir/Facturador.desktop" <<EOF
[Desktop Entry]
Type=Application
Name=Facturador
Comment=Arranca el facturador local y abre el navegador
Exec=$project_dir/abrir-facturador.sh
Path=$project_dir
Terminal=false
Categories=Office;Finance;
EOF

chmod +x "$desktop_dir/Facturador.desktop"

printf 'Acceso directo creado en: %s\n' "$desktop_dir/Facturador.desktop"
