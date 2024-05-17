FROM shin1x1/php-dev:8.3-fpm-bookworm

RUN apt-get update && apt-get -y install strace

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer
