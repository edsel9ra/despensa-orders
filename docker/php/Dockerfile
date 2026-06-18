FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev curl \
    && docker-php-ext-install pdo_mysql mbstring zip gd bcmath xml

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js 22 and pnpm
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && corepack enable && corepack prepare pnpm@latest --activate

WORKDIR /var/www

RUN useradd -G www-data,root -u 1000 -d /home/dev dev \
    && mkdir -p /home/dev/.composer && chown -R dev:dev /home/dev

USER dev
