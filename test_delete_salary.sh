#!/bin/bash

# Test script for delete salary endpoint
echo "Testing Delete Salary Endpoint"
echo "================================"

# Test with a sample employee ID
EMPLOYEE_ID="123e4567-e89b-12d3-a456-426614174000"
API_URL="http://localhost:8000/v1/salaries/employee/${EMPLOYEE_ID}"

echo "Testing DELETE request to: ${API_URL}"
echo ""

# Make DELETE request
curl -X DELETE \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -v \
  "${API_URL}"

echo ""
echo "Test completed."