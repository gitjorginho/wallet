#!/bin/bash

# Garante que as pastas de cache e storage existam e pertençam ao servidor web
mkdir -p /var/www/storage /var/www/bootstrap/cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Executa o comando principal do container (que é o php-fpm)
exec "$@"
