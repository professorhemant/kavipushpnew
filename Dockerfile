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

# Tune MPM prefork — limit idle/max workers to reduce memory (default is 5-150, we use 1-4)
RUN { \
    echo '<IfModule mpm_prefork_module>'; \
    echo '    StartServers        1'; \
    echo '    MinSpareServers     1'; \
    echo '    MaxSpareServers     2'; \
    echo '    MaxRequestWorkers   4'; \
    echo '    ServerLimit         4'; \
    echo '    MaxConnectionsPerChild 500'; \
    echo '</IfModule>'; \
} > /etc/apache2/conf-available/mpm-tuning.conf \
    && a2enconf mpm-tuning

# Set PHP memory limit and disable unused extensions
RUN { \
    echo 'memory_limit = 64M'; \
    echo 'max_execution_time = 30'; \
    echo 'upload_max_filesize = 8M'; \
    echo 'post_max_size = 8M'; \
    echo 'opcache.enable = 1'; \
    echo 'opcache.memory_consumption = 32'; \
    echo 'opcache.max_accelerated_files = 2000'; \
} > /usr/local/etc/php/conf.d/railway-optimized.ini

# Force cache invalidation before COPY (change value below to bust cache)
ARG REBUILD=20260606-v10
RUN echo "Rebuild stamp: $REBUILD"

# Copy custom WordPress files
COPY wp-config.php /var/www/html/wp-config.php
COPY wp-content /var/www/html/wp-content/

# Explicit overwrite of critical PHP files so they are NEVER served from cache
COPY wp-content/themes/kavipushp-bridals-theme/inc/admin-dashboard.php \
     /var/www/html/wp-content/themes/kavipushp-bridals-theme/inc/admin-dashboard.php
COPY wp-content/themes/kavipushp-bridals-theme/inc/admin-pages.php \
     /var/www/html/wp-content/themes/kavipushp-bridals-theme/inc/admin-pages.php
COPY reset-admin.php /var/www/html/reset-admin.php
COPY reset-pass-temp.php /var/www/html/reset-pass-temp.php
COPY fix-admin-role.php /var/www/html/fix-admin-role.php
COPY fix-theme.php /var/www/html/fix-theme.php
COPY fix-template.php /var/www/html/fix-template.php


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
