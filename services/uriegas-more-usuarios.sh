#!/bin/bash

echo "Obteniendo dirección IP..."
my_ip=$(ip route get 8.8.8.8 | awk -F"src " 'NR==1{split($2,a," ");print a[1]}')
read -p "Se usará $my_ip como la IP para configurar el servicio, ¿deseas continuar? [Yy/Nn]" -n 1 -r

echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]
then
    read -p "Inserte la dirección IP de su equipo:" my_ip
    echo ""
    if [[ ! $my_ip =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        echo "ERROR: dirección IP no válida"
        exit
    fi
fi

printf "\nCREANDO ORGANIZACIÓN DE PRUEBA\n"

# Generate a unique organization name with timestamp
timestamp=$(date +%s)
org_acronym="TESTUSERS$timestamp"
org_fullname="TESTUSERS-ORG-$timestamp"

printf "Intentando crear organización: $org_acronym\n"

# Create organization first
orgtoken=$(curl --header "Content-Type: application/json" \
  --request POST \
  --data "{    \"acronym\": \"$org_acronym\",    \"fullname\": \"$org_fullname\",    \"fathers_token\": \"/\" }" \
  http://${my_ip}:20500/auth/v1/hierarchy/create)

echo $orgtoken
orgtoken=$(echo $orgtoken | grep -o '"tokenhierarchy":"[^"]*' | grep -o '[^"]*$')

if [ -z "$orgtoken" ]; then
    printf "Error creando organización con nombre único. Intentando con organización por defecto...\n"
    
    # Try to get existing organization token
    # For now, we'll use a hardcoded fallback or ask user to provide one
    printf "Por favor, proporciona un token de organización existente o ejecuta el script con permisos para crear organizaciones.\n"
    read -p "Token de organización (deja vacío para intentar con '/'): " orgtoken
    
    if [ -z "$orgtoken" ]; then
        orgtoken="/"
        printf "Usando organización raíz: /\n"
    fi
else
    printf "Organización creada exitosamente: $org_acronym\n"
    printf "   Token: $orgtoken\n"
fi

printf "\nCREANDO DATOS DE USUARIOS DE PRUEBA - MULTIPLES USUARIOS\n"

# Generate 110 user configurations: username|password|email
user_configs=()
for i in {1..110}; do
    user_configs+=("usuario$i|Password123.|usuario$i@test.com")
done

# Array to store all created user information
declare -a user_info=()

# Create all users
for config in "${user_configs[@]}"; do
    IFS='|' read -r username password email <<< "$config"
    
    printf "\n=== CREANDO USUARIO: $username ===\n"
    printf "Email: $email\n"
    
    # Create user
    createresponse=$(curl --header "Content-Type: application/json" \
      --request POST \
      --data "{\"username\":\"$username\",\"password\":\"$password\", \"email\":\"$email\", \"tokenorg\":\"$orgtoken\"}" \
      http://${my_ip}:20500/auth/v1/users/create)

    echo $createresponse
    
    # Check if user creation was successful
    if echo "$createresponse" | grep -q "error"; then
        printf "Error creando usuario: $username\n"
        continue
    fi
    
    # Login to get tokens
    printf "Iniciando sesión para obtener tokens...\n"
    outputjson=$(curl --header "Content-Type: application/json" \
      --request POST \
      --data "{    \"user\": \"$username\",    \"password\": \"$password\" }" \
      http://${my_ip}:20500/auth/v1/users/login)

    echo $outputjson
    
    # Extract tokens
    userdata=$(echo $outputjson | grep -o '"data":"[^"]*' | grep -o '[^"]*$')
    tokenuser=$(echo $outputjson | grep -o '"tokenuser":"[^"]*' | grep -o '[^"]*$')
    apikey=$(echo $outputjson | grep -o '"apikey":"[^"]*' | grep -o '[^"]*$')
    access_token=$(echo $outputjson | grep -o '"access_token":"[^"]*' | grep -o '[^"]*$')
    
    if [ ! -z "$tokenuser" ] && [ ! -z "$apikey" ] && [ ! -z "$access_token" ]; then
        printf "Usuario creado exitosamente: $username\n"
        printf "   Token User: $tokenuser\n"
        printf "   API Key: $apikey\n"
        printf "   Access Token: ${access_token:0:20}...\n"
        user_info+=("$username|$password|$email|$tokenuser|$apikey|$access_token")
    else
        printf "Error obteniendo tokens para usuario: $username\n"
    fi
done

printf "\n=== RESUMEN DE USUARIOS CREADOS ===\n"
for info in "${user_info[@]}"; do
    IFS='|' read -r username password email tokenuser apikey access_token <<< "$info"
    printf "%s\n" "$username"
    printf "   Email: $email\n"
    printf "   Password: $password\n"
    printf "   Token User: $tokenuser\n"
    printf "   API Key: $apikey\n"
    printf "   Access Token: ${access_token:0:20}...\n\n"
done

printf "\n=== PROCESO COMPLETADO ===\n"
printf "Se crearon ${#user_info[@]} usuarios exitosamente\n"

# Create CSV file for easy import at services level
cd $(dirname "$0")  # Go to the script directory (services/)
csv_file="users_created.csv"
printf "Creando archivo CSV: $csv_file\n"

cat > $csv_file << EOF
username,password,email,tokenuser,apikey,access_token
EOF

for info in "${user_info[@]}"; do
    IFS='|' read -r username password email tokenuser apikey access_token <<< "$info"
    printf "%s,%s,%s,%s,%s,%s\n" "$username" "$password" "$email" "$tokenuser" "$apikey" "$access_token" >> $csv_file
done

printf "Archivo CSV creado en: $(pwd)/$csv_file\n"

printf "\nPuedes usar las credenciales de los usuarios para:\n"
printf "  - Login en la interfaz web: http://$my_ip:22101/\n"
printf "  - Usar las APIs con sus tokens correspondientes\n"
printf "  - Crear catálogos y subir datos con sus access_tokens\n"