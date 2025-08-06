#!/bin/bash

echo "Testing GET /api/v1/employees/{id} endpoint..."
echo "Expected: 404 Not Found (current issue)"
echo ""

# Test the problematic endpoint
curl -X 'GET' \
  'http://localhost:8000/api/v1/employees/6440bc7a-b6e0-40ff-8bbc-0fb937ad991e' \
  -H 'accept: application/ld+json' \
  -w "\nHTTP Status: %{http_code}\n" \
  -s

echo ""
echo "If this returns 404, the issue is confirmed."