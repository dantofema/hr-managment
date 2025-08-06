# Employee Salary Update Endpoint Documentation

## Overview
This endpoint allows updating the salary information for an existing employee following DDD principles and RESTful design patterns.

## Endpoint Details
- **URL**: `PATCH /api/v1/employees/{id}/salary`
- **Method**: PATCH
- **Content-Type**: application/json
- **Authentication**: Required (if applicable)

## Request Parameters

### Path Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | string (UUID) | Yes | Employee unique identifier |

### Request Body
```json
{
  "baseSalary": 75000.00,
  "bonus": 5000.00,
  "currency": "USD"
}
```

#### Request Body Fields
| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| baseSalary | float | Yes | > 0 | Employee base salary amount |
| bonus | float | No | >= 0 | Employee bonus amount (optional) |
| currency | string | Yes | 3-letter ISO code | Currency code (e.g., USD, EUR) |

## Example Requests

### Update Base Salary Only
```bash
curl -X PATCH "http://localhost:8000/api/v1/employees/123e4567-e89b-12d3-a456-426614174000/salary" \
  -H "Content-Type: application/json" \
  -d '{
    "baseSalary": 80000.00,
    "currency": "USD"
  }'
```

### Update Base Salary and Bonus
```bash
curl -X PATCH "http://localhost:8000/api/v1/employees/123e4567-e89b-12d3-a456-426614174000/salary" \
  -H "Content-Type: application/json" \
  -d '{
    "baseSalary": 75000.00,
    "bonus": 7500.00,
    "currency": "USD"
  }'
```

### Update with Different Currency
```bash
curl -X PATCH "http://localhost:8000/api/v1/employees/123e4567-e89b-12d3-a456-426614174000/salary" \
  -H "Content-Type: application/json" \
  -d '{
    "baseSalary": 65000.00,
    "bonus": 3000.00,
    "currency": "EUR"
  }'
```

## Response Examples

### Success Response (200 OK)
```json
{
  "message": "Salary updated successfully",
  "employee_id": "123e4567-e89b-12d3-a456-426614174000"
}
```

### Validation Error Response (400 Bad Request)
```json
{
  "error": "Validation failed",
  "details": {
    "baseSalary": "Base salary is required",
    "currency": "Currency must be exactly 3 characters"
  }
}
```

### Employee Not Found Response (400 Bad Request)
```json
{
  "error": "Employee not found"
}
```

### Salary Not Found Response (400 Bad Request)
```json
{
  "error": "Salary not found for this employee"
}
```

## HTTP Status Codes
| Status Code | Description |
|-------------|-------------|
| 200 | Salary updated successfully |
| 400 | Invalid input data, validation errors, or business logic errors |
| 404 | Employee not found or salary not found |
| 500 | Internal server error |

## Architecture Notes

### DDD Implementation
- **Controller**: Acts as an orchestrator, delegates business logic to Application Service
- **Command**: `UpdateSalaryCommand` encapsulates the salary update request
- **Handler**: `UpdateSalaryCommandHandler` contains the business logic
- **DTO**: `UpdateSalaryDto` handles request validation
- **Domain**: Salary is managed in the Payroll bounded context

### API Platform Integration
- Endpoint is documented in OpenAPI/Swagger at `/api/docs`
- Uses standard HTTP methods and status codes
- Follows RESTful resource naming conventions

### CORS Support
- CORS is enabled for all origins (configurable via environment)
- PATCH method is explicitly allowed
- Supports necessary headers for API consumption

## Testing the Endpoint

### Prerequisites
1. Employee must exist in the system
2. Employee must have an existing salary record (created via salary creation endpoint)
3. Valid authentication token (if authentication is enabled)

### Test Scenarios
1. **Valid Update**: Update with valid salary data
2. **Partial Update**: Update only base salary without bonus
3. **Validation Errors**: Send invalid data to test validation
4. **Non-existent Employee**: Use invalid employee ID
5. **No Existing Salary**: Try to update salary for employee without salary record

## Integration with Frontend
The endpoint can be easily integrated with frontend applications:

```javascript
// Example JavaScript integration
async function updateEmployeeSalary(employeeId, salaryData) {
  try {
    const response = await fetch(`/api/v1/employees/${employeeId}/salary`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + token
      },
      body: JSON.stringify(salaryData)
    });
    
    if (!response.ok) {
      throw new Error('Failed to update salary');
    }
    
    return await response.json();
  } catch (error) {
    console.error('Error updating salary:', error);
    throw error;
  }
}
```