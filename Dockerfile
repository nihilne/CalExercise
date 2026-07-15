FROM node:20-alpine AS build
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

FROM php:8.3-cli-alpine
WORKDIR /app

RUN apk add --no-cache sqlite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .
COPY --from=build /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader

RUN touch database/database.sqlite \
    && php artisan key:generate \
    && php artisan migrate --force

EXPOSE 9100
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=9100"]