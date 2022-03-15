FROM php:8.0-fpm-alpine AS builder

# Install composer to /usr/local/bin/composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Type docker-php-ext-install to see available extensions
RUN docker-php-ext-install pdo pdo_mysql calendar bcmath

WORKDIR /mriq

COPY ./ /mriq

RUN composer install

FROM php:8.0-fpm-alpine AS prod

WORKDIR /mriq

# Type docker-php-ext-install to see available extensions
RUN docker-php-ext-install pdo pdo_mysql calendar bcmath

COPY --from=builder /mriq /mriq
