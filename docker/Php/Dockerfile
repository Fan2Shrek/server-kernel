FROM php:8.3-fpm-alpine

ENV COMPOSER_ALLOW_SUPERUSER 1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN apk update && apk upgrade

RUN apk add --no-cache \
  acl \
  bash \
  git \
  openssh \
  rsync \
  patch \
  mysql-client \
  make \
  linux-headers

# -------------------- PHP ----------------------
ENV MEMORY_LIMIT "256M"

RUN install-php-extensions intl curl dom fileinfo mbstring zip opcache exif apcu pdo_mysql
RUN docker-php-ext-install sockets

COPY .boing/docker/php/php.ini $PHP_INI_DIR/conf.d/

# -------------------- App ----------------------
WORKDIR "/srv"

COPY . /srv

ENV DOCKER_ENABLED 0

EXPOSE 80

RUN make vendor
