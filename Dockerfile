FROM wordpress:6.7-php8.2-apache

# Install extra PHP extensions needed by plugins
RUN apt-get update && apt-get install -y \
    libicu-dev libonig-dev \
    && docker-php-ext-install intl mbstring exif \
    && rm -rf /var/lib/apt/lists/*

# Fix MPM conflict at build time
RUN a2dismod mpm_event mpm_worker 2>/dev/null; \
    a2enmod mpm_prefork 2>/dev/null; \
    rm -f /etc/apache2/mods-enabled/mpm_event.load \
          /etc/apache2/mods-enabled/mpm_event.conf \
          /etc/apache2/mods-enabled/mpm_worker.load \
          /etc/apache2/mods-enabled/mpm_worker.conf \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Enable Apache modules
RUN a2enmod rewrite headers expires

# Allow .htaccess overrides
RUN sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

# Copy custom WordPress files
COPY wp-config.php /var/www/html/wp-config.php
COPY wp-content /var/www/html/wp-content/
COPY reset-admin.php /var/www/html/reset-admin.php


# Fix permissions on wp-content
RUN chown -R www-data:www-data /var/www/html/wp-content \
    && chmod -R 775 /var/www/html/wp-content

# Install our entrypoint wrapper that fixes MPM before WordPress's entrypoint runs
COPY docker-entrypoint-wrapper.sh /usr/local/bin/docker-entrypoint-wrapper.sh
# Fix Windows CRLF line endings and make executable
RUN sed -i 's/\r//' /usr/local/bin/docker-entrypoint-wrapper.sh \
    && chmod +x /usr/local/bin/docker-entrypoint-wrapper.sh

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/docker-entrypoint-wrapper.sh"]
CMD ["apache2-foreground"]
