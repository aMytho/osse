FROM dunglas/frankenphp:1.5.0-php8.4-alpine

# Install PHP Redis extension
RUN install-php-extensions redis

WORKDIR /app
COPY composer.json composer.lock ./

# Install dependencies using Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

