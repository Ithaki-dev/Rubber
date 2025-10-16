#!/bin/bash
# Script de instalación automática para Linux con XAMPP

echo "================================================"
echo "🚗 Carpooling System - Instalación Automática"
echo "================================================"
echo ""

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Función para imprimir mensajes
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}ℹ $1${NC}"
}

# Verificar si XAMPP está instalado
echo "1. Verificando XAMPP..."
if [ -d "/opt/lampp" ]; then
    print_success "XAMPP encontrado"
else
    print_error "XAMPP no encontrado en /opt/lampp"
    echo "Por favor instala XAMPP desde: https://www.apachefriends.org/"
    exit 1
fi

# Verificar si Composer está instalado
echo ""
echo "2. Verificando Composer..."
if command -v composer &> /dev/null; then
    print_success "Composer encontrado"
else
    print_error "Composer no encontrado"
    read -p "¿Deseas instalar Composer ahora? (s/n): " install_composer
    if [ "$install_composer" == "s" ]; then
        curl -sS https://getcomposer.org/installer | php
        sudo mv composer.phar /usr/local/bin/composer
        print_success "Composer instalado"
    else
        echo "Por favor instala Composer manualmente"
        exit 1
    fi
fi

# Instalar dependencias
echo ""
echo "3. Instalando dependencias de PHP..."
composer install
if [ $? -eq 0 ]; then
    print_success "Dependencias instaladas"
else
    print_error "Error al instalar dependencias"
    exit 1
fi

# Configurar archivos de configuración
echo ""
echo "4. Configurando archivos..."

# Database config
if [ ! -f "config/database.php" ]; then
    cp config/database.example.php config/database.php
    print_success "Archivo database.php creado"
else
    print_info "database.php ya existe"
fi

# Email config
if [ ! -f "config/email.php" ]; then
    cp config/email.example.php config/email.php
    print_success "Archivo email.php creado"
else
    print_info "email.php ya existe"
fi

# Crear directorios necesarios
echo ""
echo "5. Configurando permisos..."
mkdir -p public/uploads/profiles
mkdir -p public/uploads/vehicles
mkdir -p logs

chmod -R 777 public/uploads/
chmod -R 777 logs/
print_success "Permisos configurados"

# Iniciar XAMPP si no está corriendo
echo ""
echo "6. Verificando servicios XAMPP..."
if sudo /opt/lampp/lampp status | grep -q "already running"; then
    print_info "XAMPP ya está corriendo"
else
    print_info "Iniciando XAMPP..."
    sudo /opt/lampp/lampp start
    sleep 3
    print_success "XAMPP iniciado"
fi

# Preguntar si desea importar la base de datos
echo ""
echo "7. Configuración de Base de Datos"
print_info "Ahora debes importar la base de datos en phpMyAdmin:"
echo "   1. Abre http://localhost/phpmyadmin"
echo "   2. Crea la base de datos 'carpooling_db'"
echo "   3. Selecciona la base de datos"
echo "   4. Click en 'Importar'"
echo "   5. Selecciona el archivo: $(pwd)/sql/schema.sql"
echo "   6. Click en 'Continuar'"
echo ""
read -p "¿Ya importaste el schema.sql? (s/n): " imported_schema

if [ "$imported_schema" == "s" ]; then
    read -p "¿Deseas importar los datos de prueba (seed.sql)? (s/n): " import_seed
    if [ "$import_seed" == "s" ]; then
        echo "   Importa también: $(pwd)/sql/seed.sql"
    fi
fi

# Crear enlace simbólico en htdocs (opcional)
echo ""
echo "8. Configurando acceso web..."
if [ -L "/opt/lampp/htdocs/rubber" ]; then
    print_info "Enlace simbólico ya existe"
elif [ -d "/opt/lampp/htdocs/rubber" ]; then
    print_info "Directorio rubber ya existe en htdocs"
else
    read -p "¿Deseas crear un enlace simbólico en htdocs? (s/n): " create_link
    if [ "$create_link" == "s" ]; then
        sudo ln -s "$(pwd)" /opt/lampp/htdocs/rubber
        print_success "Enlace simbólico creado"
        echo "   Acceso: http://localhost/rubber"
    fi
fi

# Resumen final
echo ""
echo "================================================"
echo "✅ Instalación Completada"
echo "================================================"
echo ""
echo "📋 Próximos pasos:"
echo ""
echo "1. Configura las credenciales en:"
echo "   - config/database.php (usar root sin password)"
echo "   - config/email.php (usar Mailtrap.io)"
echo ""
echo "2. Asegúrate de importar la base de datos:"
echo "   http://localhost/phpmyadmin"
echo ""
echo "3. Accede a la aplicación:"
echo "   http://localhost/rubber"
echo ""
echo "4. Usuario admin de prueba:"
echo "   Email: admin@carpooling.com"
echo "   Password: admin123"
echo ""
echo "📚 Documentación:"
echo "   - README.md - Documentación general"
echo "   - CONFIGURACION_XAMPP.md - Guía detallada de XAMPP"
echo "   - GUIA_PROYECTO.md - Guía de desarrollo"
echo ""
echo "================================================"
