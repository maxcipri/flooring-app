FROM php:8.1-apache

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache modules
RUN a2enmod rewrite headers

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configure Apache to listen on PORT env variable
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
