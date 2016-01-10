FROM php:fpm

WORKDIR /app

RUN apt-get update
RUN apt-get install -y \
  git \
  zlib1g-dev \
  ruby \
  ruby-dev

RUN docker-php-ext-install mbstring zip pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

RUN gem install --no-ri --no-rdoc \
  guard \
  guard-shell
