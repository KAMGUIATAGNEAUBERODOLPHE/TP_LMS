# Utilise une image officielle de PHP avec Apache
FROM php:8.2-apache

# Active l'extension PDO MySQL pour que PHP puisse parler à la base de données
RUN docker-php-ext-install pdo pdo_mysql

# Copie tous les fichiers de ton projet dans le dossier du serveur Apache
COPY . /var/www/html/

# Donne les bonnes permissions aux fichiers
RUN chown -R www-data:www-data /var/www/html/

# Expose le port 80 pour le trafic web
EXPOSE 80
