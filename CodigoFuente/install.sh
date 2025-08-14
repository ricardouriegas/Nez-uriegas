#!/bin/bash

echo "Obeteniendo dirección IP..."
my_ip=$(ip route get 8.8.8.8 | awk -F"src " 'NR==1{split($2,a," ");print a[1]}')
read -p "Se usara $my_ip como la IP para configurar el servicio, ¿deseas continuar? [Yy/Nn]" -n 1 -r

echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]
then
    read -p "Inserte la dirección IP de su equipo:" my_ip
    echo ""
    if [[ ! $my_ip =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        echo "ERROR: dirección IP no valida"
        exit
    fi
fi

echo ""
echo "Configurando archivo YML"

gateway="      URL: \"http://${my_ip}:20505\""
hostpath="      HOST_PATH: $PWD/deployer/app/"
volumehost="      - \"$PWD/deployer/app/:$PWD/deployer/app/\""

sed -i "226s#.*#$gateway#" ./docker-compose.yml
sed -i "64s#.*#$hostpath#" ./docker-compose.yml
sed -i "61s#.*#$volumehost#" ./docker-compose.yml

# appdir="$PWD/deployer/app/"
# hostpathvaluechain="      HOST_PATH: $appdir"
# volume1IDA="      - \"$appdir:$appdir\""
# volumeChunk1IDA="      - \"${appdir}dispersals/uploads/c1/:/home/Volume/chunk1/\""
# volumeChunk2IDA="      - \"${appdir}dispersals/uploads/c2/:/home/Volume/chunk2/\""
# volumeChunk3IDA="      - \"${appdir}dispersals/uploads/c3/:/home/Volume/chunk3/\""
# volumeChunk4IDA="      - \"${appdir}dispersals/uploads/c4/:/home/Volume/chunk4/\""
# volumeChunk5IDA="      - \"${appdir}dispersals/uploads/c5/:/home/Volume/chunk5/\""

# volumeChunk1IDAdown="      - \"${appdir}dispersals/downloads/c1/:/home/Volume/Results/chunk1/\""
# volumeChunk2IDAdown="      - \"${appdir}dispersals/downloads/c2/:/home/Volume/Results/chunk2/\""
# volumeChunk3IDAdown="      - \"${appdir}dispersals/downloads/c3/:/home/Volume/Results/chunk3/\""
# volumeChunk4IDAdown="      - \"${appdir}dispersals/downloads/c4/:/home/Volume/Results/chunk4/\""
# volumeChunk5IDAdown="      - \"${appdir}dispersals/downloads/c5/:/home/Volume/Results/chunk5/\""


# sed -i "368s#.*#$volume1IDA#" ./docker-compose.yml
# sed -i "369s#.*#$volumeChunk1IDA#" ./docker-compose.yml
# sed -i "370s#.*#$volumeChunk2IDA#" ./docker-compose.yml
# sed -i "371s#.*#$volumeChunk3IDA#" ./docker-compose.yml
# sed -i "372s#.*#$volumeChunk4IDA#" ./docker-compose.yml
# sed -i "373s#.*#$volumeChunk5IDA#" ./docker-compose.yml

# sed -i "383s#.*#$volume1IDA#" ./docker-compose.yml
# sed -i "384s#.*#$volumeChunk1IDAdown#" ./docker-compose.yml
# sed -i "385s#.*#$volumeChunk2IDAdown#" ./docker-compose.yml
# sed -i "386s#.*#$volumeChunk3IDAdown#" ./docker-compose.yml
# sed -i "387s#.*#$volumeChunk4IDAdown#" ./docker-compose.yml
# sed -i "388s#.*#$volumeChunk5IDAdown#" ./docker-compose.yml



docker compose up -d


