FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
  git zip unzip wget curl libpng-dev \
  libzip-dev default-mysql-client

RUN docker-php-ext-install pdo pdo_mysql zip gd

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
  && apt-get install -y nodejs

RUN a2enmod rewrite

WORKDIR /var/www

COPY . /var/www

RUN wget https://get.symfony.com/cli/installer -O - | bash \
  && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-scripts --no-autoloader --ignore-platform-req=ext-http

RUN npm install

EXPOSE 80

RUN sed -i 's!/var/www/html!/var/www/public!g' \
  /etc/apache2/sites-available/000-default.conf

CMD ["apache2-foreground"]
