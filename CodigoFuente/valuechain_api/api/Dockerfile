FROM ubuntu:20.04

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update && apt-get install -y \
	#python-software-properties \
	software-properties-common \
	apache2 \
	php \
	libapache2-mod-php \
	php-pgsql \
	php-mysql \
	php-curl \
	php-dev \
	nano \
	net-tools \
	curl \
	#libjson0 \
	#libjson0-dev \
	gcc

COPY ./configs/apache2.conf /etc/apache2/
COPY ./configs/php.ini /etc/php/7.0/apache2/

RUN a2enmod rewrite

ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid

RUN service apache2 restart
RUN rm /var/www/html/index.html

# copy the application to container
COPY ./app /var/www/html/
WORKDIR /var/www/html/
RUN touch /var/www/html/logs/server.log \
	&& chmod +w /var/www/html/logs/server.log

COPY start_service.sh /usr/local/bin/
RUN chmod u+x /usr/local/bin/start_service.sh

ENTRYPOINT /usr/local/bin/start_service.sh
