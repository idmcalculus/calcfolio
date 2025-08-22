FROM php:8.3-cli

# Install required system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    bash \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files and install dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# Copy application code
COPY src ./src

# Expose port (Railway will set PORT env var dynamically)
EXPOSE 8080

# Create startup script that uses Railway's PORT environment variable
RUN echo '#!/bin/sh' > /app/start.sh && \
    echo 'PORT=${PORT:-8080}' >> /app/start.sh && \
    echo 'echo "Starting PHP server on port $PORT"' >> /app/start.sh && \
    echo 'php -S 0.0.0.0:$PORT -t src' >> /app/start.sh && \
    chmod +x /app/start.sh

# Start PHP built-in server with dynamic port
CMD ["/app/start.sh"]