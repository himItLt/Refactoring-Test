FROM ubuntu:20.04

WORKDIR /var/www

RUN apt update && apt upgrade -y
RUN apt-get update && apt-get install -y software-properties-common
RUN LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php

RUN apt update && apt install -y php8.3

RUN apt-get install -y php8.3-cli php8.3-common php8.3-fpm php8.3-mysql php8.3-zip php8.3-gd php8.3-mbstring php8.3-curl php8.3-xml php8.3-bcmath php8.3-pgsql
RUN apt install -y nginx php8.3-fpm php8.3-intl

RUN apt-get --allow-releaseinfo-change update

# install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer

RUN rm -f /etc/nginx/sites-enabled/default

COPY main-nginx.conf /etc/nginx/sites-available/psg_security
RUN ln -s /etc/nginx/sites-available/psg_security /etc/nginx/sites-enabled/
COPY openssl.cnf /etc/ssl/openssl.cnf
COPY start.sh /start.sh
COPY php.ini /usr/local/etc/php/

RUN ["chmod", "+x", "/start.sh"]

CMD ["/start.sh"]
