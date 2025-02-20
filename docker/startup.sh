#!/bin/bash

# Make sure the public storage directory exists after storage is mounted by Docker
mkdir -p /var/www/html/storage/app/public

# Create the symlink if required
php artisan storage:link

# Run any required migrations
php artisan migrate

# Make sure permissions are acceptable for Laravel to run
chmod -R 775 /var/www/html/storage/
chown www-data:www-data /var/www/html/storage/

# Start supervisor to start the app processes
supervisord -c /etc/supervisor.conf
