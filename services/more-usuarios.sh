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

# Create organization first
orgtoken=$(curl --header "Content-Type: application/json" \
  --request POST \
  --data '{    "acronym": "TESTUSERS",    "fullname": "TESTUSERS-ORG",    "fathers_token": "/" }' \
  http://${my_ip}:20500/auth/v1/hierarchy/create)

echo $orgtoken
orgtoken=$(echo $orgtoken | grep -o '"tokenhierarchy":"[^"]*' | grep -o '[^"]*$')

if [ -z "$orgtoken" ]; then
    printf "❌ Error creando organización\n"
    exit 1
else
    printf "✅ Organización creada exitosamente\n"
    printf "   Token: $orgtoken\n"
fi

printf "\nCREANDO DATOS DE USUARIOS DE PRUEBA - MULTIPLES USUARIOS\n"

# Array of user configurations: username|password|email
user_configs=(
    "usuario1|Password123.|usuario1@test.com"
    "usuario2|Password456.|usuario2@test.com"
    "usuario3|Password789.|usuario3@test.com"
    "admin_user|AdminPass123.|admin@test.com"
    "test_user|TestPass123.|testuser@test.com"
    "demo_user|DemoPass123.|demo@test.com"
    "guest_user|GuestPass123.|guest@test.com"
    "dev_user|DevPass123.|developer@test.com"
)

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
        printf "❌ Error creando usuario: $username\n"
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
        printf "✅ Usuario creado exitosamente: $username\n"
        printf "   Token User: $tokenuser\n"
        printf "   API Key: $apikey\n"
        printf "   Access Token: ${access_token:0:20}...\n"
        user_info+=("$username|$password|$email|$tokenuser|$apikey|$access_token")
    else
        printf "❌ Error obteniendo tokens para usuario: $username\n"
    fi
done

printf "\n=== RESUMEN DE USUARIOS CREADOS ===\n"
for info in "${user_info[@]}"; do
    IFS='|' read -r username password email tokenuser apikey access_token <<< "$info"
    printf "👤 $username\n"
    printf "   Email: $email\n"
    printf "   Password: $password\n"
    printf "   Token User: $tokenuser\n"
    printf "   API Key: $apikey\n"
    printf "   Access Token: ${access_token:0:20}...\n\n"
done

printf "\n=== PROCESO COMPLETADO ===\n"
printf "Se crearon ${#user_info[@]} usuarios exitosamente\n"

# Create a summary file with all user information
summary_file="users_created_summary.txt"
printf "Creando archivo resumen: $summary_file\n"

cat > $summary_file << EOF
=== RESUMEN DE USUARIOS CREADOS ===
Fecha: $(date)
IP del servidor: $my_ip
Organización: TESTUSERS-ORG
Token de organización: $orgtoken

USUARIOS CREADOS:
EOF

for info in "${user_info[@]}"; do
    IFS='|' read -r username password email tokenuser apikey access_token <<< "$info"
    cat >> $summary_file << EOF

👤 Usuario: $username
   Email: $email
   Password: $password
   Token User: $tokenuser
   API Key: $apikey
   Access Token: $access_token

EOF
done

cat >> $summary_file << EOF

=== URLS ÚTILES ===
- Servicio de autenticación: http://$my_ip:20500/auth/v1/
- Login endpoint: http://$my_ip:20500/auth/v1/users/login
- Frontend de eSalud: http://$my_ip:22101/

=== COMANDOS CURL DE EJEMPLO ===
# Login de usuario:
curl --header "Content-Type: application/json" \\
  --request POST \\
  --data '{"user": "USUARIO_AQUI", "password": "PASSWORD_AQUI"}' \\
  http://$my_ip:20500/auth/v1/users/login

# Crear catálogo con access_token:
curl --header "Content-Type: application/json" \\
  --request POST \\
  --data '{"catalogname": "MI_CATALOGO", "dispersemode": "false", "encryption":"true", "fathers_token":"/"}' \\
  http://$my_ip:20500/pub_sub/v1/catalogs/create?access_token=ACCESS_TOKEN_AQUI
EOF

# Create CSV file for easy import
csv_file="users_created.csv"
printf "Creando archivo CSV: $csv_file\n"

cat > $csv_file << EOF
username,password,email,tokenuser,apikey,access_token
EOF

for info in "${user_info[@]}"; do
    IFS='|' read -r username password email tokenuser apikey access_token <<< "$info"
    printf "%s,%s,%s,%s,%s,%s\n" "$username" "$password" "$email" "$tokenuser" "$apikey" "$access_token" >> $csv_file
done

printf "✅ Archivo resumen creado: $summary_file\n"
printf "✅ Archivo CSV creado: $csv_file\n"

printf "\nPuedes usar las credenciales de los usuarios para:\n"
printf "  - Login en la interfaz web: http://$my_ip:22101/\n"
printf "  - Usar las APIs con sus tokens correspondientes\n"
printf "  - Crear catálogos y subir datos con sus access_tokens\n"