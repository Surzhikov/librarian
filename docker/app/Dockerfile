FROM php:8.2-fpm-alpine

# User and group
RUN addgroup -g 1000 app && adduser -u 1000 -G app -s /bin/sh -D app

# Adding PHP extensions
RUN apk add libjpeg-turbo-dev libpng-dev libwebp-dev freetype-dev libzip-dev curl-dev nss ca-certificates 
RUN docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype
RUN docker-php-ext-install gd zip pcntl pdo pdo_mysql curl

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# NodeJs
RUN apk add nodejs yarn

# Supervisor
RUN apk add supervisor supercronic htop bash


USER app
WORKDIR /var/www


#ENTRYPOINT ["tail", "-f", "/dev/null"]
#ENTRYPOINT ["supervisord"]
ENTRYPOINT "/entrypoint.sh"
