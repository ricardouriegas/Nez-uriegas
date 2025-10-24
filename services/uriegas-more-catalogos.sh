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
printf "CREANDO 110 CATALOGOS DE PRUEBA (SIN DATOS)\n"

# Generate 110 catalog configurations: name|dispersemode|encryption
catalog_configs=()
for i in {1..110}; do
    # Cycle through disperse and encryption combinations
    disperse_mode=$((($i - 1) % 2))
    encryption_mode=$(((($i - 1) / 2) % 2))
    
    if [ $disperse_mode -eq 0 ]; then
        disperse="false"
    else
        disperse="true"
    fi
    
    if [ $encryption_mode -eq 0 ]; then
        encrypt="false"
    else
        encrypt="true"
    fi
    
    catalog_configs+=("catalog-$i|$disperse|$encrypt")
done

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
        printf "Catálogo creado exitosamente: $catalog_name\n"
        printf "   Token: $tokencatalog\n"
        catalog_tokens+=("$tokencatalog|$catalog_name|$disperse_mode|$encryption_mode")
    else
        printf "Error creando catálogo: $catalog_name\n"
    fi
done

printf "\n=== RESUMEN DE CATÁLOGOS CREADOS ===\n"
for token_info in "${catalog_tokens[@]}"; do
    IFS='|' read -r token name disperse encrypt <<< "$token_info"
    printf "%s\n" "$name"
    printf "   Token: $token\n"
    printf "   Disperse: $disperse, Encryption: $encrypt\n\n"
done

printf "\n=== PROCESO COMPLETADO ===\n"
printf "Se crearon ${#catalog_tokens[@]} catálogos vacíos para pruebas\n"

# Create a CSV file with all catalog information at services level
cd $(dirname "$0")  # Go to the script directory (services/)
csv_file="catalogs_created.csv"
printf "Creando archivo CSV: $csv_file\n"

cat > $csv_file << EOF
catalog_name,token,dispersemode,encryption,explorer_url
EOF

for token_info in "${catalog_tokens[@]}"; do
    IFS='|' read -r token name disperse encrypt <<< "$token_info"
    printf "%s,%s,%s,%s,http://%s:20505/uriegas-catalog_explorer.html?catalog_token=%s\n" "$name" "$token" "$disperse" "$encrypt" "$my_ip" "$token" >> $csv_file
    printf "  - $name (Token: ${token:0:16}...)\n"
done

printf "Archivo CSV creado en: $(pwd)/$csv_file\n"
printf "\nPuedes usar los tokens de catálogos para explorar su contenido con:\n"
printf "  - Interfaz web: http://$my_ip:20505/uriegas-catalog_explorer.html\n"
printf "  - API directa: http://$my_ip:20505/uriegas-catalog_explorer.php?catalog_token=TOKEN\n"
printf "  - API de búsqueda: http://$my_ip:20505/uriegas-search_catalogs.php\n"

