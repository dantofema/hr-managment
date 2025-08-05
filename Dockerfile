FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instalar Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos de composer
COPY composer.json composer.lock* ./

# Instalar dependencias de PHP
RUN composer install --no-scripts --no-autoloader

# Copiar el código de la aplicación
COPY . .

# Completar la instalación de Composer
RUN composer dump-autoload --optimize

# Establecer permisos
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/public
RUN chmod -R 775 /var/www/html/var

# Exponer puerto
EXPOSE 8000

# Comando por defecto
CMD ["symfony", "serve", "--host=0.0.0.0", "--port=8000", "--no-tls"]
