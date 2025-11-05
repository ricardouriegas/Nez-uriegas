#!/bin/bash

echo "🚀 Building and deploying Uriegas FAIR API container..."

# Build the API container
echo "📦 Building uriegas-api container..."
docker compose build uriegas-api

# Start the API service
echo "🏃 Starting uriegas-api service..."
docker compose up -d uriegas-api

# Wait a moment for startup
echo "⏳ Waiting for API to start..."
sleep 10

# Test the API
echo "🧪 Testing API health..."
curl -f http://localhost:20506/uriegas-search_catalogs.php?action=dos_status

if [ $? -eq 0 ]; then
    echo "✅ API is running successfully!"
    echo "🌐 API available at: http://localhost:20506/uriegas-search_catalogs.php"
    echo "🔍 Test interface at: http://localhost:20506/uriegas-search_interface.html"
else
    echo "❌ API health check failed"
    echo "📋 Checking logs..."
    docker compose logs uriegas-api
fi