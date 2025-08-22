#!/bin/bash
set -e

service postgresql restart
chmod 777 /var/run/docker.sock

source /etc/apache2/envvars
# tail -F /var/log/apache2/* &
exec apache2 -D FOREGROUND