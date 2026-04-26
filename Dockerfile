FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && a2enmod rewrite

COPY app/ /var/www/html/dfsms/

RUN mkdir -p /var/www/html/logs \
    && chown -R www-data:www-data /var/www/html/logs /var/www/html/dfsms

EXPOSE 80
