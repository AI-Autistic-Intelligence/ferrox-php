FROM php:8.2-cli-alpine

WORKDIR /app
COPY . /app

# Install system dependencies and Composer
RUN apk add --no-cache curl unzip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader

EXPOSE 80

# Serve the Basic API Showcase
CMD ["php", "-S", "0.0.0.0:80", "-t", "examples/basic_api/public"]
