FROM php:8.1-fpm-trixie

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt-get update && \
    apt-get install -y ca-certificates openssl && \
    update-ca-certificates && \
    apt-get install -y zip libzip-dev && \
    docker-php-ext-configure zip && \
    docker-php-ext-install zip && \
    apt-get clean && apt-get --yes autoremove --purge && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
           /usr/share/doc/* /usr/share/groff/* /usr/share/info/* /usr/share/linda/* \
           /usr/share/lintian/* /usr/share/locale/* /usr/share/man/*

# we are injecting an SSL cert becaue of the following issue: cURL error 60: SSL certificate problem: unable to get local issuer certificate
COPY ca-certificates/*.crt /usr/local/share/ca-certificates/
RUN update-ca-certificates

RUN pecl install redis && docker-php-ext-enable redis

COPY app.ini $PHP_INI_DIR/conf.d/app.ini

RUN apt update
RUN apt install -y moreutils

# pyhton panda (Transformations : Use a Virtual Environment /opt/venv)
RUN apt-get update && \
    apt-get install --no-install-recommends --yes python3 python3-pip python3-venv && \
    python3 -m venv /opt/venv && \
    /opt/venv/bin/pip install pandas && \
    apt-get clean && apt-get --yes autoremove --purge && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
           /usr/share/doc/* /usr/share/groff/* /usr/share/info/* /usr/share/linda/* \
           /usr/share/lintian/* /usr/share/locale/* /usr/share/man/*

ENV PATH="/opt/venv/bin:$PATH"

# python panda
#RUN apt-get update && \
#    apt-get --no-install-recommends --no-install-suggests --yes --quiet install python3 python3-pip && \
#    pip install --break-system-packages pandas && \
#    apt-get clean && apt-get --yes --quiet autoremove --purge && \
#    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
