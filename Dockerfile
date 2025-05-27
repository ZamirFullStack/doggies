# Usa una imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instala extensiones necesarias para MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Habilita mod_rewrite de Apache para URL amigables si es necesario
RUN a2enmod rewrite

# Copia todos los archivos del proyecto al contenedor
COPY . /var/www/html/

# Establece permisos adecuados para Apache
RUN chown -R www-data:www-data /var/www/html \
 && find /var/www/html -type d -exec chmod 755 {} \; \
 && find /var/www/html -type f -exec chmod 644 {} \;

# Define el directorio de trabajo
WORKDIR /var/www/html

# Expón el puerto 80
EXPOSE 80
