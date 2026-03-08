FROM php:8.2-apache

# Install system dependencies + PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libwebp-dev libxml2-dev \
    libzip-dev libicu-dev libonig-dev zip unzip curl \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install \
        gd mysqli pdo_mysql xml zip opcache intl mbstring exif \
    && rm -rf /var/lib/apt/lists/*

# Fix MPM conflict — forcibly remove conflicting MPM load files, keep only prefork
RUN rm -f /etc/apache2/mods-enabled/mpm_event.load \
           /etc/apache2/mods-enabled/mpm_event.conf \
           /etc/apache2/mods-enabled/mpm_worker.load \
           /etc/apache2/mods-enabled/mpm_worker.conf \
    && a2enmod mpm_prefork 2>/dev/null || true

# Enable Apache modules
RUN a2enmod rewrite headers expires

# Apache config — allow .htaccess overrides
RUN sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

# Copy all WordPress files
COPY . /var/www/html/

# Create uploads dir (will be overridden by Railway volume)
RUN mkdir -p /var/www/html/wp-content/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/wp-content

EXPOSE 80
CMD ["apache2-foreground"]
