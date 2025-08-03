FROM php:8.0-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libonig-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install curl mbstring pdo pdo_mysql zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite

# Configure Apache for .htaccess support
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Set timezone
ENV TZ=UTC
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Configure PHP settings (defaults, can be overridden by environment variables)
RUN echo "date.timezone = \${TZ:-UTC}" >> /usr/local/etc/php/conf.d/docker-php-timezone.ini \
    && echo "memory_limit = \${PHP_MEMORY_LIMIT:-256M}" >> /usr/local/etc/php/conf.d/docker-php-memory.ini \
    && echo "upload_max_filesize = \${PHP_UPLOAD_MAX_FILESIZE:-64M}" >> /usr/local/etc/php/conf.d/docker-php-uploads.ini \
    && echo "post_max_size = \${PHP_POST_MAX_SIZE:-64M}" >> /usr/local/etc/php/conf.d/docker-php-uploads.ini

# Create entrypoint script to handle dynamic PHP configuration
RUN echo '#!/bin/bash\n\
# Update PHP configuration based on environment variables\n\
echo "date.timezone = ${TZ:-UTC}" > /usr/local/etc/php/conf.d/docker-php-timezone.ini\n\
echo "memory_limit = ${PHP_MEMORY_LIMIT:-256M}" > /usr/local/etc/php/conf.d/docker-php-memory.ini\n\
echo "upload_max_filesize = ${PHP_UPLOAD_MAX_FILESIZE:-64M}" > /usr/local/etc/php/conf.d/docker-php-uploads.ini\n\
echo "post_max_size = ${PHP_POST_MAX_SIZE:-64M}" >> /usr/local/etc/php/conf.d/docker-php-uploads.ini\n\
\n\
# Start Apache\n\
exec apache2-foreground\n\
' > /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# Copy application files
COPY . /var/www/html/

# Create data directory and set permissions
RUN mkdir -p /var/www/html/.data && \
    touch /var/www/html/.data/config.php && \
    chown -R www-data:www-data /var/www/html/.data

# Expose port 80
EXPOSE 80

# Use custom entrypoint
CMD ["/usr/local/bin/docker-entrypoint.sh"]
