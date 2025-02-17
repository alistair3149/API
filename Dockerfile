### Extensions
FROM php:8.2-apache as extensions

LABEL stage=intermediate

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        libcurl4-gnutls-dev \
        libicu-dev \
        libmcrypt-dev \
        libvpx-dev \
        libxpm-dev \
        zlib1g-dev \
        libxml2-dev \
        libexpat1-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libwebp-dev \
        libbz2-dev \
        libgmp3-dev \
        libldap2-dev \
        unixodbc-dev \
        libpng-dev \
        libpq-dev \
        libaspell-dev \
        libsnmp-dev \
        libpcre3-dev \
        libtidy-dev \
        libzip-dev \
        libonig-dev

RUN ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/local/include/

RUN docker-php-ext-install bcmath && \
    docker-php-ext-install gmp && \
    docker-php-ext-install intl && \
    docker-php-ext-install opcache && \
    docker-php-ext-install pdo_mysql && \
    docker-php-ext-install zip

RUN set -eux; \
	docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp; \
	docker-php-ext-install -j "$(nproc)" gd

RUN echo '\
opcache.enable=1\n\
opcache.memory_consumption=256\n\
opcache.interned_strings_buffer=16\n\
opcache.max_accelerated_files=16000\n\
opcache.validate_timestamps=0\n\
opcache.load_comments=Off\n\
opcache.save_comments=1\n\
opcache.fast_shutdown=0\n\
' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

### Composer
FROM php:8.2-apache as api

COPY --from=extensions /usr/local/etc/php/conf.d/docker-php-ext-bcmath.ini /usr/local/etc/php/conf.d/docker-php-ext-bcmath.ini
COPY --from=extensions /usr/local/etc/php/conf.d/docker-php-ext-intl.ini /usr/local/etc/php/conf.d/docker-php-ext-intl.ini
COPY --from=extensions /usr/local/lib/php/extensions/no-debug-non-zts-20220829/intl.so /usr/local/lib/php/extensions/no-debug-non-zts-20220829/intl.so
COPY --from=extensions /usr/local/lib/php/extensions/no-debug-non-zts-20220829/bcmath.so /usr/local/lib/php/extensions/no-debug-non-zts-20220829/bcmath.so

LABEL stage=intermediate

WORKDIR /api

# install git
RUN set -eux; \
    chown www-data:www-data /api; \
	apt-get update && \
    apt-get install -y zip unzip git

COPY --chown=www-data:www-data composer.json composer.lock /api/

COPY --chmod=777 --from=composer /usr/bin/composer /usr/bin/composer

USER www-data

RUN set -eux; \
	/usr/bin/composer install --no-dev \
   --ignore-platform-reqs \
   --no-ansi \
   --no-autoloader \
   --no-interaction \
   --no-scripts

COPY --chown=www-data:www-data / /api

RUN rm -rf storage/app/api/scunpacked-data
RUN git clone https://github.com/StarCitizenWiki/scunpacked-data --branch=master --depth=1 storage/app/api/scunpacked-data

RUN /usr/bin/composer dump-autoload --optimize --classmap-authoritative

### Final Image
FROM php:8.2-apache

USER root

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libwebp-dev \
        libpng-dev

WORKDIR /var/www/html

COPY --chown=www-data:www-data --from=api /api /var/www/html
COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY --chown=www-data:www-data --chmod=770 ./docker/start.sh /usr/local/bin/start

COPY --from=extensions /usr/local/etc/php/conf.d/*.ini /usr/local/etc/php/conf.d/
COPY --from=extensions /usr/local/lib/php/extensions/no-debug-non-zts-20220829/*.so /usr/local/lib/php/extensions/no-debug-non-zts-20220829/

RUN sed -i -e "s/extension=zip.so/;extension=zip.so/" /usr/local/etc/php/conf.d/docker-php-ext-zip.ini && \
    echo 'memory_limit = 512M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini && \
    echo 'max_execution_time = 60' >> /usr/local/etc/php/conf.d/docker-php-executiontime.ini

COPY --chown=www-data:www-data --chmod=770 ./docker/schedule.sh /usr/local/bin/schedule

RUN chmod -R u+w,g+w /var/www/html/storage; \
    a2enmod rewrite

USER www-data

RUN php artisan storage:link; \
    php artisan optimize

CMD ["/usr/local/bin/start"]
