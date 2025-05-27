# Imagen base con PHP y Apache
FROM php:8.2-apache

# Habilita mod_rewrite de Apache para URLs amigables
RUN a2enmod rewrite

# Instala extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql

# Copia todos los archivos del proyecto al contenedor
COPY . /var/www/html/

# Establece permisos correctos para Apache
RUN chown -R www-data:www-data /var/www/html && \
    find /var/www/html -type d -exec chmod 755 {} \; && \
    find /var/www/html -type f -exec chmod 644 {} \;

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Expone el puerto por el que Apache escucha
EXPOSE 80
