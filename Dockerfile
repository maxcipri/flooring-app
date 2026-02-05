FROM php:8.1-apache

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache modules
RUN a2enmod rewrite headers

# Copy application files
COPY . /var/www/html/

# Create Apache config to set CSP headers
RUN echo '<Directory /var/www/html>' > /etc/apache2/conf-available/csp-headers.conf && \
    echo '    Header always set Content-Security-Policy "frame-ancestors https://*.myshopify.com https://admin.shopify.com"' >> /etc/apache2/conf-available/csp-headers.conf && \
    echo '    Header always unset X-Frame-Options' >> /etc/apache2/conf-available/csp-headers.conf && \
    echo '</Directory>' >> /etc/apache2/conf-available/csp-headers.conf && \
    a2enconf csp-headers

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
