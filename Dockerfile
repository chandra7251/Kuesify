FROM php:8.3-cli

RUN apt-get update && apt-get install -y git libzip-dev unzip && docker-php-ext-install pdo_mysql zip && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --optimize-autoloader && npm ci && npm run build && rm -rf node_modules
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
