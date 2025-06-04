FROM php:8.1-fpm

# Set noninteractive installation
ENV DEBIAN_FRONTEND=noninteractive

# Add backports and update
RUN echo "deb http://deb.debian.org/debian bullseye-backports main" > /etc/apt/sources.list.d/backports.list \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get update

# Install dependencies
RUN apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    build-essential \
    autoconf \
    gcc \
    make \
    pkg-config \
    re2c \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip pdo pdo_mysql

# Install Phalcon
RUN git clone --depth=1 -b v5.0.0 https://github.com/phalcon/cphalcon.git \
    && cd cphalcon/build \
    && ./install \
    && docker-php-ext-enable phalcon \
    && cd ../.. \
    && rm -rf cphalcon

# Verify Phalcon installation
RUN php -m | grep -i phalcon

# Set the working directory
WORKDIR /var/www

EXPOSE 9000

CMD ["php-fpm"]