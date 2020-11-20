FROM php:7.4-apache

RUN apt-get update -y \
	&& apt-get upgrade -y \
	&& apt-get install -y git \
	&& apt-get install -y libicu-dev \
	&& apt-get install -y libfreetype6-dev \
	&& apt-get install -y libjpeg-dev \
	&& apt-get install -y libpng-dev \
	&& apt-get install -y libzip-dev \
	&& apt-get install -y libcurl4-gnutls-dev \
	&& docker-php-ext-install pdo pdo_mysql mysqli json bcmath curl \
	&& pecl install redis && docker-php-ext-enable redis \
	&& docker-php-ext-configure intl \
	&& docker-php-ext-install intl \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install -j$(nproc) gd

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
COPY . /www
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /www

RUN chown -R www-data:www-data /www \
	&& a2enmod rewrite \
	&& chmod -R 777 /www/cache /www/conf /www/files /www/lang /www/layouts \
	&& chmod 777 /www/apps \
	&& touch /www/conf/installed \
	&& composer install

EXPOSE 80
