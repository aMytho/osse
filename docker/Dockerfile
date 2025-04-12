# Build osse_web
FROM node:22-alpine as osse_web

WORKDIR /app

COPY osse-web/package*.json osse-web/pnpm-lock.yaml ./

RUN npm install -g pnpm@latest-10

RUN pnpm install

COPY osse-web .

RUN pnpm run build --configuration=production

# Build osse (API)
FROM dunglas/frankenphp:1.5.0-php8.4-alpine

# Install PHP Redis extension
RUN install-php-extensions redis

WORKDIR /app
COPY composer.json composer.lock artisan ./

# Install dependencies using Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --no-scripts --no-autoloader --no-dev --prefer-dist

COPY . .

# Now that the dependencies are installed, rerun install scripts
RUN composer install --optimize-autoloader --no-dev

# Copy osse-web
COPY --from=osse_web /app/dist /app/public/dist
