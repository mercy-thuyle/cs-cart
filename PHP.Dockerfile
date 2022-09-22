FROM php:7.4-fpm

WORKDIR /var/www/html/dev-ssm.unicloud.com.vn

RUN apt-get update; \
    apt-get install -y --no-install-recommends \
        libjpeg-dev \
        libpng-dev \
        libwebp-dev \
        libfreetype6-dev \
        libzip-dev \
        libxml2-dev \
        libc-client-dev \
        libkrb5-dev \
        libldap2-dev \
        libzmq3-dev \
        zlib1g-dev \
    ; \
    \
    debMultiarch="$(dpkg-architecture --query DEB_BUILD_MULTIARCH)"; \
    docker-php-ext-install mysqli; \
    docker-php-ext-install zip; \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
    docker-php-ext-install gd; \
    docker-php-ext-configure imap --with-kerberos --with-imap-ssl; \
    docker-php-ext-install imap; \
    docker-php-ext-configure ldap --with-libdir="lib/$debMultiarch"; \
    docker-php-ext-install ldap; \
    docker-php-ext-install exif; \
    docker-php-ext-install pcntl; \
    docker-php-ext-install posix; \
    docker-php-ext-install curl; \
    docker-php-ext-install sockets; \
    docker-php-ext-install soap; \
    # docker-php-ext-enable zmq; \
    \
# # reset a list of apt-mark
#     apt-mark auto '.*' > /dev/null; \
#     apt-mark manual $aptMarkList; \
#     ldd "$(php -r 'echo ini_get("extension_dir");')"/*.so \
#         | awk '/=>/ { print $3 }' \
#         | sort -u \
#         | xargs -r dpkg-query -S \
#         | cut -d: -f1 \
#         | sort -u \
#         | xargs -rt apt-mark manual; \
#     \
#     apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false

# Install required libs
RUN apt-get install -y --no-install-recommends \
        unzip \
        libldap-common \
        git \
        curl \
    ; \
    rm -rf /var/lib/apt/lists/*

# php.ini
RUN { \
	echo 'expose_php = Off'; \
	echo 'display_errors = Off'; \
	echo 'display_startup_errors = Off'; \
	echo 'log_errors = On'; \
	echo 'memory_limit=256M'; \
	echo 'max_execution_time=180'; \
	echo 'max_input_time=180'; \
	echo 'post_max_size=30M'; \
	echo 'upload_max_filesize=30M'; \
	echo 'date.timezone=UTC'; \
} > ${PHP_INI_DIR}/conf.d/cs-cart.ini

RUN chown -R www-data:www-data .;

