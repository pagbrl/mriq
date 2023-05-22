FROM php:8.1-fpm-alpine AS builder

# Install composer to /usr/local/bin/composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Install php extensions
RUN docker-php-ext-install pdo pdo_mysql calendar bcmath
RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
  && pecl install redis \
  && docker-php-ext-enable redis.so

WORKDIR /mriq
COPY composer.* /mriq/
RUN composer install

COPY ./ /mriq/

FROM php:8.1-fpm-alpine AS prod

WORKDIR /mriq
RUN docker-php-ext-install pdo pdo_mysql calendar bcmath
RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
  && pecl install redis \
  && docker-php-ext-enable redis.so

COPY --from=builder /mriq /mriq
