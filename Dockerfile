FROM node:20-alpine AS build
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

FROM php:8.4-cli-alpine
WORKDIR /app

RUN apk add --no-cache sqlite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .
COPY --from=build /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]

EXPOSE 8080
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]