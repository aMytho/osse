FROM dunglas/frankenphp:1.5.0-php8.4-alpine

# Install PHP Redis extension
RUN install-php-extensions redis

# Generate encryption key and cache the views.
RUN frankenphp php-cli artisan key:generate
RUN frankenphp php-cli artisan view:cache
