# Stage 1: Build PHP with extensions
FROM php:8.3-fpm-alpine as builder

# Install system dependencies for pgsql and Caddy download
RUN apk add --no-cache \
    postgresql-dev \
    $PHPIZE_DEPS \
    curl

# Install pdo_pgsql extension
RUN docker-php-ext-install pdo_pgsql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files and install dependencies
COPY composer.json composer.lock /app/
RUN composer install --no-dev --optimize-autoloader

# Copy application code
COPY src ./src

# Copy example env for reference if needed
COPY .env.example ./.env.example

# Stage 2: Setup Caddy and final image
FROM php:8.3-fpm-alpine

# Install Caddy (adjust version if needed)
RUN apk add --no-cache caddy --repository=http://dl-cdn.alpinelinux.org/alpine/edge/community

# Install only necessary runtime extensions
RUN apk add --no-cache libpq # Runtime dependency for pdo_pgsql
COPY --from=builder /usr/local/lib/php/extensions/no-debug-non-zts-20230831/pdo_pgsql.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/pdo_pgsql.so
RUN docker-php-ext-enable pdo_pgsql

WORKDIR /app

# Copy built vendor directory and application code from builder stage
COPY --from=builder /app/vendor ./vendor
COPY --from=builder /app/src ./src
# Copy .env.example if needed, but rely on Railway env vars primarily
# COPY --from=builder /app/.env.example ./.env.example

# Copy Caddyfile configuration
COPY <<EOF /etc/caddy/Caddyfile
{
    # Default listener address
    http_port 9000
}

:9000 {
    root * /app/src # Assuming your index.php is in src/
    php_fastcgi unix//run/php/php-fpm.sock
    file_server
}
EOF

# Expose Caddy's port
EXPOSE 9000

# Start Caddy and PHP-FPM
# Use supervisord or a simple script if more complex startup needed
# For simplicity, run Caddy which will keep the container alive
CMD ["caddy", "run", "--config", "/etc/caddy/Caddyfile", "--adapter", "caddyfile"]
