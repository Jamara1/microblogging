# Dockerfile
FROM php:8.3-cli

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    zip unzip git curl \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Asegurar permisos correctos
RUN chmod -R 777 storage bootstrap/cache

# Instalar dependencias de Composer
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Generar la clave de la aplicación
RUN php artisan key:generate

# Exponer el puerto 8000
EXPOSE 8000

# Definir comando de inicio (Laravel esperará a LocalStack)
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
