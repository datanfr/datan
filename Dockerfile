# define php version
FROM php:7.4-apache

#install php and docker lib
RUN apt-get update && apt-get install -y git libzip-dev libicu-dev libpng-dev libjpeg-dev zlib1g-dev && \
    docker-php-ext-configure intl && \
    docker-php-ext-install mysqli pdo pdo_mysql zip intl && \
    docker-php-ext-configure gd \
    --with-jpeg \
    && docker-php-ext-install gd && \
    docker-php-ext-enable pdo_mysql

COPY conf/000-default.conf /etc/apache2/sites-available/000-default.conf

COPY conf/entrypoint.sh /
RUN chmod +x /entrypoint.sh

COPY . /var/www/html

RUN a2enmod rewrite && \

#install and run composer
    curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
    mv composer.phar /usr/local/bin/composer && \
    composer update && composer install && \

#install and run npm 
    apt-get install -y \
    software-properties-common \
    npm && \
    npm install npm@latest -g && \
    npm install n -g && \
    n latest 
    
    RUN npm install && \

#install and run grunt
    npm install -g grunt-cli && \
    apt-get install -y ruby-dev && \
    npm install grunt-contrib-sass --save-dev && \
    gem install sass && \
    npm install --save-dev sass && \
    apt-get install -y ca-certificates fonts-liberation libappindicator3-1 libasound2 libatk-bridge2.0-0 libatk1.0-0 libc6 libcairo2 libcups2 libdbus-1-3 libexpat1 libfontconfig1 libgbm1 libgcc1 libglib2.0-0 libgtk-3-0 libnspr4 libnss3 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 lsb-release wget xdg-utils && \

# TO DO vérifier pour les erreurs de connexion
    grunt --force && \

    export DATABASE_HOST=${DATABASE_HOST} && \
    export DATABASE_USERNAME=${DATABASE_USERNAME} && \
    export DATABASE_PASSWORD=${DATABASE_PASSWORD} 