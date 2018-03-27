FROM php:7.1-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    wget \
    jq \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get autoremove -y

RUN echo 'deb http://ftp.debian.org/debian jessie-backports main' >> /etc/apt/sources.list \
    && apt-get update \
    && apt-get install -y certbot -t jessie-backports

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

# Set timezone
RUN rm /etc/localtime
RUN ln -s /usr/share/zoneinfo/Europe/Paris /etc/localtime
RUN "date"

# Type docker-php-ext-install to see available extensions
RUN docker-php-ext-install pdo pdo_mysql calendar bcmath mbstring

RUN echo "memory_limit=4096M" > /usr/local/etc/php/conf.d/docker-docker-memory-limit.ini