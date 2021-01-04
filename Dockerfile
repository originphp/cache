#
# OriginPHP Framework
# Copyright 2018 - 2021 Jamiel Sharief.
#
# Licensed under The MIT License
# The above copyright notice and this permission notice shall be included in all copies or substantial
# portions of the Software.
#
# @copyright    Copyright (c) Jamiel Sharief
# @link          https://www.originphp.com
# @license      https://opensource.org/licenses/mit-license.php MIT License
#
FROM ubuntu:18.04
LABEL maintainer="Jamiel Sharief"
LABEL version="1.0.0"

# Setup Enviroment

ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV DATE_TIMEZONE UTC
ENV DEBIAN_FRONTEND=noninteractive

# Best Practice : Cache Busting - Prevent cache issues run as one command
# @link https://docs.docker.com/develop/develop-images/dockerfile_best-practices/

RUN apt-get update && apt-get install -y \
    curl \
    git \
    nano \
    unzip \
    wget \
    zip \
    php \
    php-cli \
    php-apcu \
    php-common \
    php-curl \
    php-imap \
    php-intl \
    php-json \
    php-mbstring \
    php-opcache \
    php-pear \
    php-readline \
    php-soap \
    php-xml \
    php-zip \
    php-dev \
    php-memcached \
 && rm -rf /var/lib/apt/lists/*


COPY . /var/www
RUN chmod -R 0775 /var/www
WORKDIR /var/www

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-interaction

RUN echo 'apc.enable_cli=1' >>  /etc/php/7.2/cli/php.ini

# Install Redis
RUN pecl install redis
RUN echo 'extension=redis.so' >> /etc/php/7.2/cli/php.ini

CMD ["/usr/sbin/apache2ctl", "-DFOREGROUND"]