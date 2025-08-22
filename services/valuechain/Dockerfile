# Dockerfile
FROM ddomizzi/apache:docker

RUN apt-get update

ARG DEBIAN_FRONTEND=noninteractive

RUN service apache2 restart

COPY www /var/www/html/
