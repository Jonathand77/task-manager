#!/bin/bash

echo "==================================="
echo "Task Manager Backend - Setup"
echo "==================================="
echo ""

# Verificar si composer.json existe
if [ ! -f "composer.json" ]; then
    echo "❌ composer.json no encontrado"
    exit 1
fi

# Instalar dependencias
echo "📦 Instalando dependencias PHP..."
composer install

# Crear .env si no existe
if [ ! -f ".env" ]; then
    echo "📝 Creando archivo .env..."
    cp .env.example .env
fi

# Crear directorio de logs
mkdir -p logs

echo ""
echo "==================================="
echo "✅ Setup completado!"
echo "==================================="
echo ""
echo "Próximos pasos:"
echo "1. Editar .env con tus datos de BD"
echo "2. Ejecutar migraciones de BD"
echo "3. Iniciar el servidor: php -S localhost:8000 -t public/"
echo ""
