#!/bin/bash

# Start PHP-FPM
php-fpm -D

# Start Supervisor
exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf 