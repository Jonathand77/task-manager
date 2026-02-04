@echo off
REM Task Manager Backend - Setup para Windows

echo ===================================
echo Task Manager Backend - Setup
echo ===================================
echo.

REM Verificar si composer.json existe
if not exist "composer.json" (
    echo Composer.json no encontrado
    exit /b 1
)

REM Instalar dependencias
echo Installing PHP dependencies...
call composer install

REM Crear .env si no existe
if not exist ".env" (
    echo Creating .env file...
    copy .env.example .env
)

REM Crear directorio de logs
if not exist "logs" mkdir logs

echo.
echo ===================================
echo Setup completado!
echo ===================================
echo.
echo Proximos pasos:
echo 1. Editar .env con tus datos de BD
echo 2. Ejecutar migraciones de BD
echo 3. Iniciar el servidor: php -S localhost:8000 -t public/
echo.
pause
