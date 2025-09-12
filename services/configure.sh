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

printf "\n\n\nCREANDO DATOS DE USUARIO DE PRUEBA\n"

orgtoken=$(curl --header "Content-Type: application/json" \
  --request POST \
  --data '{    "acronym": "TEST",    "fullname": "TESTORG",    "fathers_token": "/" }' \
  http://${my_ip}:20500/auth/v1/hierarchy/create)


echo $orgtoken

orgtoken=$(echo $orgtoken | grep -o '"tokenhierarchy":"[^"]*' | grep -o '[^"]*$')

curl --header "Content-Type: application/json" \
  --request POST \
  --data '{"username":"testuser","password":"TestUser123.", "email":"test@test.com", "tokenorg":"'$orgtoken'"}' \
  http://${my_ip}:20500/auth/v1/users/create


outputjson=$(curl --header "Content-Type: application/json" \
  --request POST \
  --data '{    "user": "testuser",    "password": "TestUser123." }' \
  http://${my_ip}:20500/auth/v1/users/login)

echo $outputjson
  
userdata=$(echo $outputjson | grep -o '"data":"[^"]*' | grep -o '[^"]*$')
tokenuser=$(echo $outputjson | grep -o '"tokenuser":"[^"]*' | grep -o '[^"]*$')
apikey=$(echo $outputjson | grep -o '"apikey":"[^"]*' | grep -o '[^"]*$')
access_token=$(echo $outputjson | grep -o '"access_token":"[^"]*' | grep -o '[^"]*$')

echo "Token user " $tokenuser
echo "API key " $apikey
echo "Access token " $access_token

printf "\n\n\nCONFIGURANDO SERVICIO DE ALMACENAMIENTO\n"

for i in 1 2 3 4 5
do
    docker compose exec storage$i sh -c "mkdir -p /var/www/html/c && chown -R www-data /var/www/html/c"
    docker compose exec storage$i sh -c "mkdir -p /var/www/html/abekeys && chown -R www-data /var/www/html/abekeys"
done

docker compose exec apigateway sh -c "mkdir -p /var/www/html/log/ && chown -R www-data /var/www/html/log/"
docker compose exec auth sh -c "mkdir -p /var/www/html/log/ &&  chown -R www-data /var/www/html/log/"
docker compose exec metadata sh -c "mkdir -p /var/www/html/log/ &&  chown -R www-data /var/www/html/log/"
docker compose exec pub_sub sh -c "mkdir -p /var/www/html/log/ && chown -R www-data /var/www/html/log/"
docker compose exec value-chain-api sh -c "mkdir -p /var/www/html/logs/ &&  chown -R www-data /var/www/html/geb/cfg-files"
docker compose exec value-chain-api sh -c "mkdir -p /var/www/html/logs/ &&  chown -R www-data /var/www/html/logs"
docker compose exec frontend sh -c "mkdir -p /var/www/html/painal/downloads &&  chown -R www-data /var/www/html/painal/downloads"

curl http://$my_ip:20505/configNodes.php?deleteNodes=true

for i in 06 07 08 09 10
do
    curl -X POST -F "capacity=40000000000" -F "memory=2000000000" -F "url=$my_ip:200$i/" http://$my_ip:20505/configNodesPost.php
    echo " "
done


printf "\n\n\nCREANDO DATOS DE CATALOGO DE PRUEBA\n"


outputjson=$(curl --header "Content-Type: application/json" \
  --request POST \
  --data '{ "catalogname": "TESTCATALOG", "dispersemode": "false", "encryption":"true", "fathers_token":"/"}' \
  http://${my_ip}:20500/pub_sub/v1/catalogs/create?access_token=$access_token)

echo $outputjson
tokencatalog=$(echo $outputjson | grep -o '"tokencatalog":"[^"]*' | grep -o '[^"]*$')
#tokencatalog=$(echo $outputjson | /usr/bin/jq --raw-output '.tokencatalog')

if [ -z "$tokencatalog" ]
then
  outputjson=$(curl --header "Content-Type: application/json" \
  --request POST \
  --data '{ "catalogname": "TESTCATALOG", "dispersemode": "false", "encryption":"true", "fathers_token":"/"}' \
  http://${my_ip}:20500/pub_sub/v1/catalogs/create?access_token=$access_token)

  #tokencatalog=$(echo $outputjson | /usr/bin/jq --raw-output '.tokencatalog')
  tokencatalog=$(echo $outputjson | grep -o '"tokencatalog":"[^"]*' | grep -o '[^"]*$')
else
      echo "\$my_var is NOT NULL"
fi

echo "Token catalog " $tokencatalog



printf "\n\n\nCARGANDO DATOS DE PRUEBA\n"

cd sincronizador

sed -i "6s#.*#$my_ip:20505/#" ./config.db
sed -i "7s#.*#$my_ip:20500/#" ./config.db

docker compose cp config.db deployer:/home/app/
docker compose cp config.db decipher:/app

echo "java -jar Upload.jar $tokenuser $apikey $tokencatalog SINGLE bob 2 $PWD/../datosprueba TESTORG true $access_token true false 4"

java -jar Upload.jar $tokenuser $apikey $tokencatalog SINGLE bob 2 $PWD/../datosprueba TESTORG true $access_token true false 4

cd ..

printf "\n\n\nCONFIGURANDO MICROSERVICIOS DE PRUEBA\n"

docker pull ddomizzi/microservice:base
docker pull ddomizzi/tc:balancer
docker pull ddomizzi/cleaner:header
docker pull ddomizzi/dicomtorgb:v1
docker pull ddomizzi/deteccion:pulmon

docker tag ddomizzi/microservice:base microservice:base
docker tag ddomizzi/tc:balancer tc:balancer
docker tag ddomizzi/dicomtorgb:v1 dicomtorgb:v1

curl --header "Content-Type: application/json" \
  --request POST \
  --data '{"name":"Anonimizacion", "command":"python3 /code/process_dir.py --input @I --outfolder \"@D\" --save dicom", "image":"ddomizzi/cleaner:header", "description":"Anonimizacion de imagenes DICOM" }' \
  "http://${my_ip}:20510/api/v1/buildingblocks?access_token=$tokenuser"

curl --header "Content-Type: application/json" \
  --request POST \
  --data '{"name":"ToRGB", "command":"python3 /code/dicom2rgb.py @I @D/@L", "image":"dicomtorgb:v1", "description":"Convierte imagenes DICOM en RGB" }' \
  "http://${my_ip}:20510/api/v1/buildingblocks?access_token=$tokenuser"

curl --header "Content-Type: application/json" \
  --request POST \
  --data '{"name":"DetectorPulmon", "command":"python3 /code/detectorPulmones.py @I @D/@L", "image":"ddomizzi/deteccion:pulmon", "description":"Deteccion de anomalias en pulmon" }' \
  "http://${my_ip}:20510/api/v1/buildingblocks?access_token=$tokenuser"


printf "\n\nPara diseñar un servicio de eSalud, dirigase a http://${my_ip}:22101/ e inicie sesión con los siguientes datos:\nCorreo electronico: test@test.com\nContraseña: TestUser123."