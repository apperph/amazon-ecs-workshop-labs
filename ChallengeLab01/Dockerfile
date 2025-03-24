FROM php:7.4-apache
WORKDIR /var/www/html
COPY index.php .
RUN chown www-data:www-data /var/www/html -R && chmod 755 /var/www/html
EXPOSE 80