FROM php:8.2-apache

# Habilita mod_rewrite para URLs amigables
RUN a2enmod rewrite

# Copia todo el proyecto al contenedor
COPY . /var/www/html/

# Establece permisos correctos
RUN chown -R www-data:www-data /var/www/html && \
    find /var/www/html -type d -exec chmod 755 {} \; && \
    find /var/www/html -type f -exec chmod 644 {} \;

# Define explícitamente index.php como página de inicio
RUN echo "DirectoryIndex index.php" > /etc/apache2/conf-available/doggies.conf && \
    a2enconf doggies

# Exponer el puerto por defecto de Apache
EXPOSE 80
