FROM php:8.2-apache

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Instalar extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql && docker-php-ext-enable pdo_mysql

# Copiar archivos del proyecto
COPY . /var/www/html/

# Establecer permisos adecuados
RUN chown -R www-data:www-data /var/www/html && \
    find /var/www/html -type d -exec chmod 755 {} \; && \
    find /var/www/html -type f -exec chmod 644 {} \;

# Crear archivo de configuración para pasar variables de entorno
RUN echo "PassEnv DATABASE_URL" > /etc/apache2/conf-available/envvars.conf && \
    a2enconf envvars

# Configurar DirectoryIndex
RUN echo "DirectoryIndex index.php" > /etc/apache2/conf-available/doggies.conf && \
    a2enconf doggies

# Exponer el puerto 80
EXPOSE 80
