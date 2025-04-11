# define php version
FROM php:7.4-apache

# Install system dependencies with proper cleanup
RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    zlib1g-dev \
    software-properties-common \
    ruby-dev \
    ca-certificates \
    fonts-liberation \
    libappindicator3-1 \
    libasound2 \
    libatk-bridge2.0-0 \
    libatk1.0-0 \
    libc6 \
    libcairo2 \
    libcups2 \
    libdbus-1-3 \
    libexpat1 \
    libfontconfig1 \
    libgbm1 \
    libgcc1 \
    libglib2.0-0 \
    libgtk-3-0 \
    libnspr4 \
    libnss3 \
    libpango-1.0-0 \
    libpangocairo-1.0-0 \
    libstdc++6 \
    libx11-6 \
    libx11-xcb1 \
    libxcb1 \
    libxcomposite1 \
    libxcursor1 \
    libxdamage1 \
    libxext6 \
    libxfixes3 \
    libxi6 \
    libxrandr2 \
    libxrender1 \
    libxss1 \
    libxtst6 \
    lsb-release \
    wget \
    xdg-utils \
    npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure intl \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install mysqli pdo pdo_mysql zip intl gd \
    && docker-php-ext-enable pdo_mysql

# Configure Apache
COPY conf/000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Install Node.js with specific version
RUN npm install -g npm@8.19.3 \
    && npm install -g n \
    && n 16.18.1

# Install frontend build tools
RUN npm install -g grunt-cli@1.4.3 \
    && gem install sass -v 3.7.4

# Copy dependency files first (better layer caching)
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader

COPY package.json package-lock.json ./
RUN npm install \
    && npm install grunt-contrib-sass --save-dev \
    && npm install --save-dev sass \
    && npm install rollup@2.79.1 --save-dev \
    && npm install grunt-rollup --save-dev \
    && npm install @rollup/plugin-node-resolve --save-dev \
    && npm install chartjs-plugin-datalabels --save-dev

# Copy the rest of the application
COPY . .

# Complete Composer installation
RUN composer dump-autoload --optimize

# Run Grunt (with --force to bypass Chart.js compatibility warnings)
RUN grunt --force

# Set environment variables
ENV DATABASE_HOST=${DATABASE_HOST} \
    DATABASE_USERNAME=${DATABASE_USERNAME} \
    DATABASE_PASSWORD=${DATABASE_PASSWORD}

# Setup entrypoint
COPY conf/entrypoint.sh /
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]