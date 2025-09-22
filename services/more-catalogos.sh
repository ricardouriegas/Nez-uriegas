echo "Obeteniendo dirección IP..."
my_ip=$(ip route get 8.8.8.8 | awk -F"src " 'NR==1{split($2,a," ");print a[1]}')
read -p "Se usara $my_ip como la IP para configurar el servicio, ¿deseas continuar? [Yy/Nn]" -n 1 -r
outputjson=$(curl --header "Content-Type: application/json" \
  --request POST \
  --data '{    "user": "testuser",    "password": "TestUser123." }' \
  http://${my_ip}:20500/auth/v1/users/login)

# variables used for creating test catalog
userdata=$(echo $outputjson | grep -o '"data":"[^"]*' | grep -o '[^"]*$')
tokenuser=$(echo $outputjson | grep -o '"tokenuser":"[^"]*' | grep -o '[^"]*$')
apikey=$(echo $outputjson | grep -o '"apikey":"[^"]*' | grep -o '[^"]*$')
access_token=$(echo $outputjson | grep -o '"access_token":"[^"]*' | grep -o '[^"]*$')

# here i use chatgpt to create all permutations of catalogs
printf "CREANDO DATOS DE CATALOGO DE PRUEBA - TODAS LAS PERMUTACIONES\n"

# Array of catalog configurations: name|dispersemode|encryption
catalog_configs=(
    "without-disperse-and-without-encryption|false|false"
    "without-disperse-and-with-encryption|false|true"
    "with-disperse-and-without-encryption|true|false"
    "with-disperse-and-with-encryption|true|true"
)

# Array to store all created catalog tokens
declare -a catalog_tokens=()

# Create all catalog permutations
for config in "${catalog_configs[@]}"; do
    IFS='|' read -r catalog_name disperse_mode encryption_mode <<< "$config"
    
    printf "\n=== CREANDO CATALOGO: $catalog_name ===\n"
    printf "Dispersemode: $disperse_mode, Encryption: $encryption_mode\n"
    
    outputjson=$(curl --header "Content-Type: application/json" \
      --request POST \
      --data "{ \"catalogname\": \"$catalog_name\", \"dispersemode\": \"$disperse_mode\", \"encryption\":\"$encryption_mode\", \"fathers_token\":\"/\"}" \
      http://${my_ip}:20500/pub_sub/v1/catalogs/create?access_token=$access_token)

    echo $outputjson
    tokencatalog=$(echo $outputjson | grep -o '"tokencatalog":"[^"]*' | grep -o '[^"]*$')
    
    if [ -z "$tokencatalog" ]; then
        printf "Reintentando creación del catálogo...\n"
        outputjson=$(curl --header "Content-Type: application/json" \
          --request POST \
          --data "{ \"catalogname\": \"$catalog_name\", \"dispersemode\": \"$disperse_mode\", \"encryption\":\"$encryption_mode\", \"fathers_token\":\"/\"}" \
          http://${my_ip}:20500/pub_sub/v1/catalogs/create?access_token=$access_token)
        
        tokencatalog=$(echo $outputjson | grep -o '"tokencatalog":"[^"]*' | grep -o '[^"]*$')
    fi
    
    if [ ! -z "$tokencatalog" ]; then
        printf "✅ Catálogo creado exitosamente: $catalog_name\n"
        printf "   Token: $tokencatalog\n"
        catalog_tokens+=("$tokencatalog|$catalog_name|$disperse_mode|$encryption_mode")
    else
        printf "❌ Error creando catálogo: $catalog_name\n"
    fi
done

printf "\n=== RESUMEN DE CATÁLOGOS CREADOS ===\n"
for token_info in "${catalog_tokens[@]}"; do
    IFS='|' read -r token name disperse encrypt <<< "$token_info"
    printf "📁 $name\n"
    printf "   Token: $token\n"
    printf "   Disperse: $disperse, Encryption: $encrypt\n\n"
done

printf "\n=== CARGANDO DATOS DE PRUEBA EN TODOS LOS CATÁLOGOS ===\n"

cd sincronizador

sed -i "6s#.*#$my_ip:20505/#" ./config.db
sed -i "7s#.*#$my_ip:20500/#" ./config.db

docker compose cp config.db deployer:/home/app/
docker compose cp config.db decipher:/app

# Upload data to each created catalog
for token_info in "${catalog_tokens[@]}"; do
    IFS='|' read -r tokencatalog catalog_name disperse_mode encryption_mode <<< "$token_info"
    
    printf "\n--- Subiendo datos al catálogo: $catalog_name ---\n"
    printf "Token: $tokencatalog\n"
    printf "Configuración: Disperse=$disperse_mode, Encryption=$encryption_mode\n"
    
    upload_command="java -jar Upload.jar $tokenuser $apikey $tokencatalog SINGLE bob 2 $PWD/../datosprueba TESTORG true $access_token true false 4"
    printf "Comando: $upload_command\n"
    
    # Execute the upload
    java -jar Upload.jar $tokenuser $apikey $tokencatalog SINGLE bob 2 $PWD/../datosprueba TESTORG true $access_token true false 4
    
    upload_result=$?
    if [ $upload_result -eq 0 ]; then
        printf "✅ Datos subidos exitosamente al catálogo: $catalog_name\n"
    else
        printf "❌ Error subiendo datos al catálogo: $catalog_name (código: $upload_result)\n"
    fi
done

printf "\n=== PROCESO COMPLETADO ===\n"
printf "Se crearon ${#catalog_tokens[@]} catálogos con todas las permutaciones de dispersemode y encryption\n"

# Create a summary file with all catalog information
summary_file="catalog_permutations_summary.txt"
printf "Creando archivo resumen: $summary_file\n"

cat > $summary_file << EOF
=== RESUMEN DE CATÁLOGOS CREADOS ===
Fecha: $(date)
IP del servidor: $my_ip
Usuario: testuser
Token de usuario: $tokenuser

CATÁLOGOS CREADOS:
EOF

for token_info in "${catalog_tokens[@]}"; do
    IFS='|' read -r token name disperse encrypt <<< "$token_info"
    cat >> $summary_file << EOF

📁 Catálogo: $name
   Token: $token
   Dispersemode: $disperse
   Encryption: $encrypt
   URL de exploración: http://$my_ip:20505/uriegas-catalog_explorer.html?catalog_token=$token

EOF
    printf "  - $name (Token: ${token:0:16}...)\n"
done

cat >> $summary_file << EOF

=== URLS ÚTILES ===
- Explorador de catálogos: http://$my_ip:20505/uriegas-catalog_explorer.html
- Búsqueda FAIR: http://$my_ip:20505/uriegas-search_interface.html
- API de búsqueda: http://$my_ip:20505/uriegas-search_catalogs_fair.php
- API de exploración: http://$my_ip:20505/uriegas-catalog_explorer.php

=== COMANDOS CURL DE EJEMPLO ===
# Buscar catálogos:
curl "http://$my_ip:20505/uriegas-search_catalogs_fair.php?q=without"

# Explorar contenido de un catálogo:
curl "http://$my_ip:20505/uriegas-catalog_explorer.php?catalog_token=TOKEN_AQUI"
EOF

printf "✅ Archivo resumen creado: $summary_file\n"
printf "\nPuedes usar los tokens de catálogos para explorar su contenido con:\n"
printf "  - Interfaz web: http://$my_ip:20505/uriegas-catalog_explorer.html\n"
printf "  - API directa: http://$my_ip:20505/uriegas-catalog_explorer.php?catalog_token=TOKEN\n"

