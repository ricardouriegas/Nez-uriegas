#Crear imagenes de contenedor
docker build -f DockerfileKullaBase -t "kulla:base" .
docker build -f DockerfileKBSegmentadorIDA -t "kulla:code3" .
docker build -f DockerfileKBIntegradorIDA -t "kulla:decode2" .
