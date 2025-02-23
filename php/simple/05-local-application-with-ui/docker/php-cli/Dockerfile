# This file is part of the b24sdk-examples package.
#
# © Maksim Mesilov <mesilov.maxim@gmail.com>
#
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.

FROM php:8.3-cli-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache unzip libpq-dev git icu-dev $PHPIZE_DEPS \
    && docker-php-ext-install bcmath intl \
    && docker-php-ext-enable bcmath intl

# Install and enable Excimer extension
# https://www.mediawiki.org/wiki/Excimer
RUN pecl install excimer \
    && docker-php-ext-enable excimer

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer --quiet

# Set Composer environment variable
ENV COMPOSER_ALLOW_SUPERUSER 1