#!/bin/bash

until nc -z pgsql 5432; do
    echo "Aguardando PostgreSQL..."
    sleep 1
done

php artisan config:clear
php artisan cache:clear
php artisan key:generate --force
php artisan migrate --force

php-fpm