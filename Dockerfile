FROM php:7.4-apache

WORKDIR /var/www/html

COPY . /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install

RUN docker-php-ext-install mysqli pdo_mysql

EXPOSE 80

CMD ["apache2-foreground"]
