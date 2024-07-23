# Gunakan image resmi PHP dengan Apache
FROM php:8.0-apache

# Set working directory
WORKDIR /var/www/html

# Install dependensi
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy seluruh kode proyek ke dalam container
COPY . .

# Install dependensi PHP menggunakan Composer
RUN composer install --no-dev --optimize-autoloader

# Copy file konfigurasi Apache
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf

# Enable mod_rewrite
RUN a2enmod rewrite

# Expose port 80 untuk Apache
EXPOSE 80

# Jalankan Apache di foreground
CMD ["apache2-foreground"]
