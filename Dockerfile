FROM php:8.2-apache

# Installation des dépendances et de l'extension PostgreSQL pour PHP
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Modification du port d'Apache pour s'adapter à Render (qui préfère souvent le port 8080 en interne)
RUN sed -i 's/Listen 80/Listen 8080/g' /etc/apache2/ports.conf
RUN sed -i 's/<VirtualHost \*:80>/<VirtualHost \*:8080>/g' /etc/etc/apache2/sites-available/000-default.conf 2>/dev/null || sed -i 's/<VirtualHost \*:80>/<VirtualHost \*:8080>/g' /etc/apache2/sites-enabled/000-default.conf

# Copie des fichiers de l'application
COPY . /var/www/html/

# Attribution des droits à Apache
RUN chown -R www-data:www-data /var/www/html/

EXPOSE 8080
