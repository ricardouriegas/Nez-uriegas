## Deploy
### download file
https://drive.google.com/file/d/1hiD4TIwQfwUPYwyHeuqdAbF2GbeLK-mZ/view?usp=sharing

### Importar imagen myserver.tar - myserver:update
docker import myserver2.tar myserver:update

### Ejecutar con:
docker run -p 10101:80 -v /home/x/Documents/www/:/home/x/Documents/www/  -v /var/run/docker.sock:/var/run/docker.sock -it --name myserver2 myserver:update  /bin/bash

/home/x/Documents/www/: = path to your local folder www  

### Entrar al contenedor y ejecutar
service apache2 restart
service postgresql restart
chmod 777 /var/run/docker.sock

### Salir del contenedor dejandolo en segundo plano
ctrl + p + q



## Enter to postgres db by terminal
su postgres
psql -l
psql db_name