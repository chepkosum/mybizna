#!/bin/sh

cd /var/www/html 

cp .env.example .env

sed -i 's/DB_HOST=.*/DB_HOST=mariadb/g' .env 
sed -i 's/DB_USERNAME=.*/DB_USERNAME=root/g' .env
sed -i 's/DB_PORT=.*/DB_PORT=3306/g' .env 

cat /var/www/html/.env

rm -rf Modules/*
rm composer.lock

composer install --no-interaction

composer require mybizna/isp:24.3.004 --no-interaction

echo '----------'
echo '-----mariadb-----'

mysql -u root -h mariadb -e "CREATE DATABASE IF NOT EXISTS mybizna;"
mysql -u root -h mariadb -e "SHOW DATABASES;"

php artisan key:generate

php artisan migrate 

