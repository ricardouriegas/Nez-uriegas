#!/bin/bash

# Borrar archivos y keys
find /var/www/html/abekeys/ ! -name 'upload.php' -type f -exec rm -f {} +
find /var/www/html/c/ ! -name 'upload.php' -type f -exec rm -f {} +

