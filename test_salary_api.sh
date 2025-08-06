#!/bin/bash

echo "Testing Salary API with ApiResource..."
echo "======================================"

# Test if the server is running
echo "1. Testing if server is accessible..."
curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/api/doc || echo "Server might not be running"

echo ""
echo "2. Testing GET /api/v1/salaries (Get all salaries)..."
curl -X GET "http://localhost:8000/api/v1/salaries" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -w "\nHTTP Status: %{http_code}\n" \
  -s

echo ""
echo "3. Testing POST /api/v1/salaries (Create salary)..."
curl -X POST "http://localhost:8000/api/v1/salaries" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "employee_id": "123e4567-e89b-12d3-a456-426614174000",
    "base_salary": 50000.00,
    "bonus": 5000.00,
    "currency": "USD"
  }' \
  -w "\nHTTP Status: %{http_code}\n" \
  -s

echo ""
echo "4. Testing GET /api/v1/salaries/employee/{employeeId}..."
curl -X GET "http://localhost:8000/api/v1/salaries/employee/123e4567-e89b-12d3-a456-426614174000" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -w "\nHTTP Status: %{http_code}\n" \
  -s

echo ""
echo "5. Testing Swagger documentation access..."
curl -s -o /dev/null -w "Swagger Doc HTTP Status: %{http_code}\n" http://localhost:8000/api/doc

echo ""
echo "Test completed!"