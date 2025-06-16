# Verwende ein offizielles PHP-Image mit Apache
FROM php:8.3-apache

# Arbeitsverzeichnis im Container setzen
WORKDIR /var/www/html

# Abhängigkeiten installieren
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Composer installieren
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Projekt-Dateien kopieren
COPY . /var/www/html

# Composer-Abhängigkeiten installieren
RUN composer install --no-dev --optimize-autoloader

# Apache-Konfiguration anpassen, um .htaccess zu erlauben
RUN a2enmod rewrite
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Berechtigungen setzen
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Port freigeben
EXPOSE 80

# Startbefehl
CMD ["apache2-foreground"]
