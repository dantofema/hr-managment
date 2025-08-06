#!/bin/bash

echo "Testing salary update endpoint..."

# First, let's check if there are any employees to update
echo "Fetching employees list..."
curl -X GET "http://localhost:8000/api/v1/employees" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json"

echo -e "\n\nTesting salary update with a sample UUID..."
# Test updating salary for an employee (using a sample UUID)
curl -X PATCH "http://localhost:8000/api/v1/employees/123e4567-e89b-12d3-a456-426614174000" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "salary": 75000.50
  }'

echo -e "\n\nTesting salary validation with invalid salary..."
# Test with invalid salary (negative)
curl -X PATCH "http://localhost:8000/api/v1/employees/123e4567-e89b-12d3-a456-426614174000" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "salary": -1000
  }'

echo -e "\n\nTesting salary validation with non-numeric salary..."
# Test with non-numeric salary
curl -X PATCH "http://localhost:8000/api/v1/employees/123e4567-e89b-12d3-a456-426614174000" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "salary": "invalid"
  }'

echo -e "\n\nTesting combined update (name and salary)..."
# Test updating both name and salary
curl -X PATCH "http://localhost:8000/api/v1/employees/123e4567-e89b-12d3-a456-426614174000" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Updated",
    "salary": 80000.00
  }'

echo -e "\n\nTest completed!"