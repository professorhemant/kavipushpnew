FROM wordpress:6.7-php8.2-apache

# Install extra PHP extensions needed by plugins
RUN apt-get update && apt-get install -y \
    libicu-dev libonig-dev \
    && docker-php-ext-install intl mbstring exif \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers expires

# Allow .htaccess overrides
RUN sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy custom WordPress files (themes, plugins, uploads, wp-config)
COPY wp-config.php /var/www/html/wp-config.php
COPY wp-content /var/www/html/wp-content/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html/wp-content \
    && chmod -R 775 /var/www/html/wp-content

EXPOSE 80
CMD ["apache2-foreground"]
