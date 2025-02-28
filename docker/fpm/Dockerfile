FROM php:8.1-fpm

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt update && apt install -y zip libzip-dev && \
    docker-php-ext-configure zip && \
    docker-php-ext-install zip

RUN pecl install redis && docker-php-ext-enable redis

COPY app.ini $PHP_INI_DIR/conf.d/app.ini

RUN apt update
RUN apt install -y moreutils

# python panda
RUN apt-get update && \
    apt-get --no-install-recommends --no-install-suggests --yes --quiet install python3 python3-pip && \
    pip install --break-system-packages pandas && \
    apt-get clean && apt-get --yes --quiet autoremove --purge && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
