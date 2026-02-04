# Usar imagen oficial de PHP 8.2 CLI
FROM php:8.2-cli

# Instalar extensiones PHP necesarias
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpq-dev \
        curl \
        unzip \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /app

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader

# Exponer puerto
EXPOSE 8000

# Comando por defecto (servidor incorporado)
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
