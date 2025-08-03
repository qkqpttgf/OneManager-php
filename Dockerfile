FROM php:8.0-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libonig-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install curl mbstring pdo pdo_mysql zip

# Copy application files
COPY . /var/www/html/

# Create data directory and set permissions
RUN mkdir -p /var/www/html/.data && \
    touch /var/www/html/.data/config.php && \
    chown -R www-data:www-data /var/www/html/.data

# Expose port 80
EXPOSE 80
