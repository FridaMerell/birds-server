FROM php:8.3-apache

RUN apt-get update && apt-get install -y curl libicu-dev libzip-dev \
    && docker-php-ext-install intl pdo_mysql opcache zip \
    && a2enmod rewrite \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf \
    && sed -i 's|Listen 80|Listen 8080|' /etc/apache2/ports.conf \
    && sed -i 's|:80>|:8080>|' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

COPY . .

RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-scripts --optimize-autoloader --no-interaction \
    && mkdir -p var/cache var/log \
    && chown -R www-data:www-data var/

EXPOSE 8080

CMD su -s /bin/sh www-data -c "APP_ENV=prod php bin/console cache:warmup" && apache2-foreground