FROM php:8.0.10-fpm

RUN apt-get update && apt-get install -y \
    git \
    openssl \
    libpq-dev \
    unzip \
    vim \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev && \
    docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ && \
    docker-php-ext-install gd

COPY "memory-limit-php.ini" "/usr/local/etc/php/conf.d/memory-limit-php.ini"

RUN echo "$(curl -sS https://composer.github.io/installer.sig) -" > composer-setup.php.sig \
        && curl -sS https://getcomposer.org/installer | tee composer-setup.php | sha384sum -c composer-setup.php.sig \
        && php composer-setup.php && rm composer-setup.php* \
        && chmod +x composer.phar && mv composer.phar /usr/bin/composer

RUN pecl install redis && docker-php-ext-enable redis

RUN docker-php-ext-install pdo pdo_pgsql
RUN docker-php-ext-install exif