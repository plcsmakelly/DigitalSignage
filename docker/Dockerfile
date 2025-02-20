FROM php:7.4.10-apache

RUN apt-get update
RUN apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev libonig-dev zlib1g-dev libzip-dev zip unzip nano libxml2-dev libmagickcore-dev libmagickwand-dev imagemagick libheif1 libheif-dev ghostscript supervisor
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install -j$(nproc) gd
RUN docker-php-ext-install json
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install mysqli
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install zip
RUN docker-php-ext-install xml
RUN pecl install imagick && docker-php-ext-enable imagick

RUN a2enmod rewrite
COPY docker/000-default.conf /etc/apache2/sites-enabled/000-default.conf

COPY docker/php.ini /usr/local/etc/php/php.ini
COPY docker/policy.xml /etc/ImageMagick-6/policy.xml

COPY docker/supervisor.conf /etc/supervisor.conf
COPY docker/queue.conf /etc/supervisor/conf.d/queue.conf
COPY docker/apache.conf /etc/supervisor/conf.d/apache.conf

COPY docker/startup.sh /usr/local/bin/startup.sh

WORKDIR /var/www/html

RUN cd /tmp && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && php composer-setup.php --install-dir /usr/local/bin --filename composer && rm /tmp/composer-setup.php

COPY --chown=www-data:www-data src /var/www/html

CMD ["/usr/local/bin/startup.sh"]
