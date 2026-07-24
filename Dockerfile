FROM php:8.3-apache


WORKDIR /var/www/html


RUN docker-php-ext-install \
    mysqli \
    pdo \
    pdo_mysql


RUN a2enmod rewrite


COPY . .


COPY apache.conf \
/etc/apache2/sites-available/000-default.conf


RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf


RUN sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' \
/etc/apache2/sites-available/000-default.conf


RUN chown -R www-data:www-data /var/www/html


COPY docker-entrypoint.sh /usr/local/bin/


RUN chmod +x /usr/local/bin/docker-entrypoint.sh


EXPOSE 8080


ENTRYPOINT ["docker-entrypoint.sh"]


CMD ["apache2-foreground"]