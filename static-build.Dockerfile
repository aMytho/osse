FROM --platform=linux/amd64 dunglas/frankenphp:static-builder

# Copy your app
WORKDIR /go/src/app/dist/app
COPY . .

# Install the dependencies
RUN composer install --ignore-platform-reqs --no-dev -a --optimize-autoloader
# Optimize for production 
RUN php artisan optimize
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache
RUN php artisan event:cache
RUN php artisan key:generate

# Build the static binary
WORKDIR /go/src/app/
RUN EMBED=dist/app/ PHP_VERSION=8.4.4 PHP_EXTENSIONS=apcu,bcmath,calendar,ctype,curl,dom,exif,fileinfo,filter,gd,iconv,intl,mbregex,mbstring,opcache,openssl,pcntl,pdo,phar,posix,readline,redis,session,sockets,sodium,sqlite3,ssh2,tokenizer,uuid,xml,xsl,yaml,zip,zlib,zstd PHP_EXTENSION_LIBS=bzip2,freetype,libavif,libjpeg,liblz4,libwebp,libzip,curl,icu,libiconv,libpng,libsodium,libxml2,openssl,postgresql,readline,zlib,zstd,onig,libxslt,libssh2,nghttp2 ./build-static.sh
