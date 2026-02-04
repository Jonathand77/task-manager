# Usar imagen oficial de PHP 8.2 con Apache
FROM php:8.2-cli

# Instalar herramientas necesarias y dependencias del sistema
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpq-dev \
        git \
        curl \
        unzip \
        gcc \
        make \
        autoconf \
        pkg-config \
        re2c \
        libpcre2-dev \
        libssl-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && pecl channel-update pecl.php.net \
    && pecl install cphalcon || true \
    && docker-php-ext-enable phalcon || true \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /app

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias PHP (intento tolerante durante build)
RUN COMPOSER_PROCESS_TIMEOUT=2000 composer install --no-dev --optimize-autoloader || true

# Exponer puerto
EXPOSE 8000

# Comando por defecto (usar router script)
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public", "public/index.php"]
