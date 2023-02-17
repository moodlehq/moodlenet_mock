FROM php:8.1-apache
WORKDIR /var/www/html

COPY site_httpd.conf /etc/apache2/sites-available/site_httpd.conf

#COPY localhost+1.pem /etc/apache2/ssl/localhost+1.pem
#COPY localhost+1-key.pem /etc/apache2/ssl/localhost+1-key.pem

ADD certs /etc/apache2/ssl/
ADD html /var/www/html/

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf &&\
    a2enmod rewrite &&\
    a2enmod ssl &&\
    a2dissite 000-default &&\
    a2ensite site_httpd &&\
    service apache2 restart


COPY index.php index.php
EXPOSE 443
