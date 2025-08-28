# Dockerfile
FROM php:8.2-apache

# System deps for Composer/Google API/PHPMailer
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
 && docker-php-ext-install zip \
 && a2enmod rewrite headers \
 && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# App code
WORKDIR /var/www/html
COPY . .

# Install PHP deps (skip dev)
RUN composer install --no-interaction --no-progress --prefer-dist --no-dev

# Apache port (we'll use 8080 and tell Render the service port is 8080)
ENV APACHE_PORT=8080
RUN sed -ri -e 's!^Listen 80$!Listen ${APACHE_PORT}!g' /etc/apache2/ports.conf \
    && sed -ri -e 's!VirtualHost \*:80!VirtualHost \*:${APACHE_PORT}!g' /etc/apache2/sites-available/000-default.conf

EXPOSE 8080
CMD ["apache2-foreground"]
