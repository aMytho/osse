FROM dunglas/frankenphp:1.11.1-php8.4-alpine

# ---------- PHP extensions ----------
RUN install-php-extensions redis

# Music codecs
RUN apk update && apk add vorbis-tools flac

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# ---------- working dir ----------
WORKDIR /app

# Copy osse web
COPY --from=osse-web /web/dist osse-web/dist

WORKDIR /app/osse-core
# ---------- install deps (cached) ----------
COPY composer.json composer.lock artisan ./
RUN composer install --no-scripts --no-autoloader --no-dev --prefer-dist

# Copy app files. Run install sccripts
COPY . .
RUN composer install --optimize-autoloader --no-dev

# ---------- permissions ----------
RUN mkdir -p /data && chown -R www-data:www-data /data

WORKDIR /app

CMD ["frankenphp", "run", "--config", "Caddyfile"]
