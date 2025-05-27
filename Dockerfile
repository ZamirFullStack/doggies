FROM php:8.2-apache

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Instalar extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql && docker-php-ext-enable pdo_mysql

# Copiar archivos del proyecto
COPY . /var/www/html/

# Establecer permisos
RUN chown -R www-data:www-data /var/www/html && \
    find /var/www/html -type d -exec chmod 755 {} \; && \
    find /var/www/html -type f -exec chmod 644 {} \;

# Configurar DirectoryIndex
RUN echo "DirectoryIndex index.php" > /etc/apache2/conf-available/doggies.conf && \
    a2enconf doggies

# Instalar Composer y dependencias
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    cd /var/www/html && composer install --no-dev --optimize-autoloader

# Exponer puerto
EXPOSE 80
