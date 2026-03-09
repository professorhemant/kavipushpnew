#!/bin/bash
set -e

# Fix Apache MPM conflict — run before WordPress entrypoint
a2dismod mpm_event mpm_worker 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true
rm -f /etc/apache2/mods-enabled/mpm_event.load \
      /etc/apache2/mods-enabled/mpm_event.conf \
      /etc/apache2/mods-enabled/mpm_worker.load \
      /etc/apache2/mods-enabled/mpm_worker.conf

# Configure Apache port (Railway sets $PORT dynamically)
APACHE_PORT=${PORT:-80}
sed -i "s/^Listen 80$/Listen ${APACHE_PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${APACHE_PORT}>/g" /etc/apache2/sites-enabled/*.conf 2>/dev/null || true

# Delegate to the official WordPress entrypoint
exec /usr/local/bin/docker-entrypoint.sh "$@"
