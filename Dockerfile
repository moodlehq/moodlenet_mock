FROM php:8.1-apache

COPY docker/entrypoint.sh /entrypoint.sh
COPY app /var/www

RUN apt-get update \
    && apt-get install -y apt-transport-https gnupg unzip\
    && curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash \
    && apt-get install -y symfony-cli \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && apt-get purge -y --auto-remove -o APT:::AutoRemove::RecommendsImportant=false

RUN symfony check:requirements

WORKDIR /var/www
RUN composer install -n \
    && rm -rf /root/.composer

EXPOSE 443
CMD ["symfony", "server:start", "--port=443", "--p12=/opt/ssl/certs/moodlenet.p12"]
ENTRYPOINT ["/entrypoint.sh"]
