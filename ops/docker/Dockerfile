# Optimized Multi-stage Dockerfile for Ferrox PHP (RoadRunner/Swoole Memory-Resident)
FROM php:8.3-cli-alpine AS builder

# Install system dependencies and PHP extensions
RUN apk add --no-cache git unzip libzip-dev \
    && docker-php-ext-install zip opcache pdo_mysql pcntl sockets

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install RoadRunner (Application Server)
COPY --from=spiralscout/roadrunner:2023.3 /usr/bin/rr /usr/bin/rr

WORKDIR /app
COPY composer.json composer.lock ./
# Copy all local modules
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Production Stage
FROM php:8.3-cli-alpine

RUN apk add --no-cache ca-certificates libzip \
    && docker-php-ext-install opcache pdo_mysql pcntl sockets

COPY --from=builder /usr/bin/rr /usr/bin/rr
COPY --from=builder /app /app

WORKDIR /app

# Expose RoadRunner HTTP port
EXPOSE 8080

# Run the memory-resident server
CMD ["rr", "serve", "-c", ".rr.yaml"]
