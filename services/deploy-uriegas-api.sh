#!/bin/bash

echo "Building and deploying Uriegas FAIR API container..."

# Check if docker-compose is available
if ! command -v docker &> /dev/null; then
    echo "ERROR: Docker is not installed or not in PATH"
    exit 1
fi

# Check if we're in the correct directory
if [ ! -f "docker-compose.yml" ]; then
    echo "ERROR: docker-compose.yml not found. Please run this script from the services directory."
    exit 1
fi

# Build the API container
echo "Building uriegas-api container..."
docker compose build uriegas-api 2>/dev/null || docker-compose build uriegas-api

if [ $? -ne 0 ]; then
    echo "ERROR: Failed to build uriegas-api container"
    echo "INFO: Trying alternative build method..."
    docker build -t uriegas-api:latest ./uriegas-api/
    if [ $? -ne 0 ]; then
        echo "ERROR: Build failed with both methods"
        exit 1
    fi
fi

# Check if required database containers are running
echo "Checking database containers..."
REQUIRED_DBS=("db_metadata" "db_pub_sub" "db_auth")
for db in "${REQUIRED_DBS[@]}"; do
    if ! docker ps --format '{{.Names}}' | grep -q "$db"; then
        echo "WARNING: Database container $db is not running. Starting it..."
        docker compose up -d "$db" 2>/dev/null || docker-compose up -d "$db"
        sleep 3
    else
        echo "OK: Database container $db is running"
    fi
done

# Start the API service
echo "Starting uriegas-api service..."
docker compose up -d uriegas-api 2>/dev/null || docker-compose up -d uriegas-api

if [ $? -ne 0 ]; then
    echo "ERROR: Failed to start uriegas-api service"
    exit 1
fi

# Wait a moment for startup
echo "Waiting for API to start..."
sleep 10

# Check if container is running
if docker ps --format '{{.Names}}' | grep -q "uriegas-api"; then
    echo "OK: Container is running"
else
    echo "ERROR: Container failed to start"
    echo "Checking logs..."
    docker compose logs uriegas-api
    exit 1
fi

# Test the API health
echo "Testing API health..."
HEALTH_RESPONSE=$(curl -s -w "\n%{http_code}" http://localhost:20506/uriegas-search_catalogs.php?action=dos_status 2>/dev/null)
HTTP_CODE=$(echo "$HEALTH_RESPONSE" | tail -n 1)
RESPONSE_BODY=$(echo "$HEALTH_RESPONSE" | head -n -1)

if [ "$HTTP_CODE" = "200" ]; then
    echo "OK: API health check passed (HTTP $HTTP_CODE)"
    echo ""
    echo "SUCCESS: Deployment successful!"
    echo ""
    echo "API Endpoints:"
    echo "   - Main API: http://localhost:20506/uriegas-search_catalogs.php"
    echo "   - Test Interface: http://localhost:20506/uriegas-search_interface.html"
    echo "   - DoS Status: http://localhost:20506/uriegas-search_catalogs.php?action=dos_status"
    echo "   - Get Usernames: http://localhost:20506/uriegas-search_catalogs.php?action=get_usernames"
    echo ""
    echo "Run Tests:"
    echo "   bash ./uriegas-api/src/uriegas-testcases/test_search_catalogs.sh"
    echo "   bash ./uriegas-api/src/uriegas-testcases/test_dos_simple.sh"
    echo ""
    echo "Container Status:"
    docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep -E "NAMES|uriegas-api|db_"
    echo ""
    echo "View Logs:"
    echo "   docker logs -f services-uriegas-api-1"
else
    echo "ERROR: API health check failed (HTTP $HTTP_CODE)"
    echo "Response: $RESPONSE_BODY"
    echo ""
    echo "Troubleshooting:"
    echo "   1. Check container logs: docker logs services-uriegas-api-1"
    echo "   2. Check container status: docker ps -a | grep uriegas-api"
    echo "   3. Verify databases are running: docker ps | grep db_"
    echo "   4. Try manual health check: curl -v http://localhost:20506/uriegas-search_catalogs.php?action=dos_status"
    echo ""
    echo "Recent container logs:"
    docker compose logs --tail=20 uriegas-api 2>/dev/null || docker-compose logs --tail=20 uriegas-api
    exit 1
fi