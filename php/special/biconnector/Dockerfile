FROM php:8.2-apache

# Install required packages for building extensions
RUN apt-get update && \
    apt-get install -y \
        libsqlite3-dev \
        sqlite3 \
        libicu-dev \
        pkg-config \
        git \
        unzip \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_sqlite intl bcmath

# Install composer globally
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

# Copy all source files to /var/www (NOT to /var/www/html!)
WORKDIR /var/www

COPY . /var/www

# Install dependencies
# RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Create the data directory if it does not exist (for a clean build)
RUN mkdir -p /var/www/data

# Generate SQLite database
# RUN sqlite3 /var/www/data/demo.db < /var/www/sql/schema.sql

RUN chown -R www-data:www-data /var/www/data

# Clean the web root just in case (remove everything except the public folder, if present)
RUN rm -rf /var/www/html/*

# Copy only the contents of the public directory to the web root
RUN cp -r /var/www/public/* /var/www/html/

# Important: permissions for the data directory (if the application needs to write)
RUN chown -R www-data:www-data /var/www/data