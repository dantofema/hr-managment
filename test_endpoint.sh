#!/bin/bash

echo "Testing the employees endpoint..."
echo "URL: http://localhost:8000/api/v1/employees?page=1"
echo ""

# Test the endpoint with API Platform format
curl -X GET "http://localhost:8000/api/v1/employees?page=1" \
  -H "Accept: application/ld+json" \
  -H "Content-Type: application/json" \
  -w "\nHTTP Status: %{http_code}\n" \
  -s

echo ""
echo "Testing with application/json format:"
curl -X GET "http://localhost:8000/api/v1/employees?page=1" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -w "\nHTTP Status: %{http_code}\n" \
  -s

echo ""
echo "Test completed."