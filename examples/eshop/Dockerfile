# ==============================================================================
# 🛒 Ferrox Enterprise E-Shop - Multi-Stage FrankenPHP / Swoole Dockerfile
# ==============================================================================
FROM dunglas/frankenphp:1-php8.3-alpine AS base

# Install OS dependencies & PHP extensions
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    linux-headers \
    $PHPIZE_DEPS \
    && docker-php-ext-install pdo pdo_pgsql zip pcntl bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy dependency manifests
COPY composer.json composer.lock* ./

# Copy full application source
COPY . .

# Set environment
ENV FRANKENPHP_CONFIG="worker /app/examples/eshop/bin/server.php"
ENV PORT=8080
ENV APP_ENV=production

EXPOSE 8080

HEALTHCHECK --interval=10s --timeout=3s --retries=3 \
    CMD curl -f http://localhost:8080/health/live || exit 1

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
