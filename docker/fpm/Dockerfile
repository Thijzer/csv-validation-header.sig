FROM php:8.0-fpm

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt update && apt install -y zip libzip-dev && \
    docker-php-ext-configure zip && \
    docker-php-ext-install zip

RUN pecl install redis && docker-php-ext-enable redis

COPY app.ini $PHP_INI_DIR/conf.d/app.ini

RUN apt update
RUN apt install -y moreutils

RUN apt-get update && \
    apt-get --no-install-recommends --no-install-suggests --yes --quiet install python3 python3-pip && \
    pip install pandas && \
    apt-get clean && apt-get --yes --quiet autoremove --purge && \
    rm -rf  /var/lib/apt/lists/* /tmp/* /var/tmp/* \
            /usr/share/doc/* /usr/share/groff/* /usr/share/info/* /usr/share/linda/* \
            /usr/share/lintian/* /usr/share/locale/* /usr/share/man/*