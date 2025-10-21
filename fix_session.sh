#!/bin/bash
# Script para reemplazar $this->session-> por Session:: en todos los controladores

cd /home/bob/Github/Rubber/controllers

# Reemplazar en todos los archivos PHP
for file in *.php; do
    echo "Procesando $file..."
    sed -i 's/\$this->session->/Session::/g' "$file"
done

echo "✅ Reemplazo completado en todos los controladores"
