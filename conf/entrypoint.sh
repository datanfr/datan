#!/bin/bash

sed -i 's/${DATABASE_HOST}/'"$DATABASE_HOST"'/g' /etc/apache2/sites-available/000-default.conf
sed -i 's/${MYSQL_USER}/'"$MYSQL_USER"'/g' /etc/apache2/sites-available/000-default.conf
sed -i 's/${DATABASE_USER}/'"$MYSQL_USER"'/g' /etc/apache2/sites-available/000-default.conf
sed -i 's/${DATABASE_USERNAME}/'"$MYSQL_USER"'/g' /etc/apache2/sites-available/000-default.conf
sed -i 's/${DATABASE_NAME}/'"$MYSQL_DATABASE"'/g' /etc/apache2/sites-available/000-default.conf
sed -i 's/${MYSQL_PASSWORD}/'"$MYSQL_PASSWORD"'/g' /etc/apache2/sites-available/000-default.conf
sed -i 's/${DATABASE_PASSWORD}/'"$MYSQL_PASSWORD"'/g' /etc/apache2/sites-available/000-default.conf
sed -i 's/${MYSQL_DATABASE}/'"$MYSQL_DATABASE"'/g' /etc/apache2/sites-available/000-default.conf
sed -i 's#${BASE_URL}#'"$BASE_URL"'#g' /etc/apache2/sites-available/000-default.conf
sed -i 's/${API_KEY_NOBG}/'"$API_KEY_NOBG"'/g' /etc/apache2/sites-available/000-default.conf
sed -i 's/${API_KEY_MAILJET}/'"$API_KEY_MAILJET"'/g' /etc/apache2/sites-available/000-default.conf
sed -i 's/${API_KEY_SECRETE_MAILJET}/'"$API_KEY_SECRETE_MAILJET"'/g' /etc/apache2/sites-available/000-default.conf
sed -i 's#${COMPOSER_AUTOLOAD}#'"$COMPOSER_AUTOLOAD"'#g' /etc/apache2/sites-available/000-default.conf

exec "$@"