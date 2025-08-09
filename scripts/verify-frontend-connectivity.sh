#!/bin/bash

# Frontend Connectivity Verification Script
# This script verifies that the frontend server is accessible from Docker containers

echo "=== Frontend Connectivity Verification ==="
echo "Testing frontend accessibility from containers..."

# Test 1: Check if frontend service is running
echo "1. Checking if node service is running..."
docker-compose ps node

# Test 2: Test HTTP connectivity from app container
echo "2. Testing HTTP connectivity from app container..."
HTTP_STATUS=$(docker-compose exec -T app curl -s -o /dev/null -w "%{http_code}" http://node:5173)
if [ "$HTTP_STATUS" = "200" ]; then
    echo "✅ SUCCESS: Frontend accessible from app container (HTTP $HTTP_STATUS)"
else
    echo "❌ FAILED: Frontend not accessible from app container (HTTP $HTTP_STATUS)"
    exit 1
fi

# Test 3: Verify HTML content is served
echo "3. Verifying HTML content is served correctly..."
CONTENT=$(docker-compose exec -T app curl -s http://node:5173 | head -1)
if [[ "$CONTENT" == *"<!DOCTYPE html>"* ]]; then
    echo "✅ SUCCESS: HTML content is being served correctly"
else
    echo "❌ FAILED: HTML content not served correctly"
    exit 1
fi

# Test 4: Check Vite development server is running
echo "4. Checking Vite development server logs..."
docker-compose logs node | grep -q "ready in"
if [ $? -eq 0 ]; then
    echo "✅ SUCCESS: Vite development server is running"
else
    echo "❌ FAILED: Vite development server not running properly"
    exit 1
fi

# Test 5: Verify port exposure
echo "5. Verifying port exposure..."
PORT_CHECK=$(docker-compose port node 5173)
if [ ! -z "$PORT_CHECK" ]; then
    echo "✅ SUCCESS: Port 5173 is properly exposed ($PORT_CHECK)"
else
    echo "❌ FAILED: Port 5173 not exposed"
    exit 1
fi

echo ""
echo "=== All Tests Passed! ==="
echo "Frontend is now accessible from Docker containers."
echo "The 403 Forbidden error has been resolved."