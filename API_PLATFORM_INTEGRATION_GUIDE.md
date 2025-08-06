# API Platform Integration Guide for Custom Controllers

## Overview
This guide shows how to properly integrate custom controllers with API Platform to ensure they appear in the `/api/docs` Swagger UI documentation with versioned API paths.

## Key Requirements Met
✅ Custom controllers integrated with API Platform using proper attributes  
✅ Versioned API paths (`/api/v1/...`)  
✅ Endpoints appear correctly in `/api/docs` documentation  
✅ Proper separation of concerns (ApiResource handles routing, Controller handles business logic)

## Implementation Examples

### 1. Entity-Based ApiResource with Custom Controller (Employee Status Change)

**File: `src/ApiResource/Employee.php`**
```php
<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;

#[ApiResource(
    uriTemplate: '/v1/employees',
    operations: [
        new GetCollection(),
        new Get(uriTemplate: '/v1/employees/{id}'),
        new Patch(
            uriTemplate: '/v1/employees/{id}/status',
            controller: 'App\Api\Controller\ChangeEmployeeStatusController'
        )
    ]
)]
class Employee
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public string $department,
        public string $role,
        public string $status
    ) {
    }

    public static function fromEntity(EmployeeEntity $employee): self
    {
        return new self(
            id: (string) $employee->getId(),
            name: (string) $employee->getName(),
            email: (string) $employee->getEmail(),
            department: $employee->getDepartment()->value,
            role: $employee->getRole()->value,
            status: $employee->getStatus()->value
        );
    }
}
```

**File: `src/Api/Controller/ChangeEmployeeStatusController.php`**
```php
<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Application\Employee\Command\ChangeEmployeeStatus\ChangeEmployeeStatusCommand;
use App\Domain\Employee\ValueObject\EmployeeStatus;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use ValueError;

class ChangeEmployeeStatusController extends AbstractController
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private ValidatorInterface $validator
    ) {
    }

    public function __invoke(string $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['status'])) {
            return new JsonResponse(['error' => 'Status is required'],
                Response::HTTP_BAD_REQUEST);
        }

        try {
            $newStatus = EmployeeStatus::from($data['status']);
        } catch (ValueError $e) {
            return new JsonResponse([
                'error' => 'Invalid status. Valid statuses are: ACTIVE, INACTIVE, TERMINATED'
            ], Response::HTTP_BAD_REQUEST);
        }

        $command = new ChangeEmployeeStatusCommand($id, $newStatus);

        try {
            $this->messageBus->dispatch($command);

            return new JsonResponse([
                'message' => 'Employee status changed successfully',
                'employee_id' => $id,
                'new_status' => $newStatus->value
            ]);
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
```

### 2. Standalone ApiResource for Custom Operations (Delete Employee)

**File: `src/ApiResource/DeleteEmployee.php`**
```php
<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;

#[ApiResource(
    uriTemplate: '/v1/employees/{uuid}',
    operations: [
        new Delete(
            uriTemplate: '/v1/employees/{uuid}',
            controller: 'App\Api\Controller\DeleteEmployeeController'
        )
    ]
)]
class DeleteEmployee
{
    public function __construct(
        public string $uuid
    ) {
    }
}
```

**File: `src/Api/Controller/DeleteEmployeeController.php`**
```php
<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Application\Employee\Command\DeleteEmployee\DeleteEmployeeCommand;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class DeleteEmployeeController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
    }

    public function __invoke(string $uuid): JsonResponse
    {
        $command = new DeleteEmployeeCommand($uuid);

        try {
            $this->messageBus->dispatch($command);

            return new JsonResponse([
                'message' => 'Employee deleted successfully',
                'employee_id' => $uuid
            ]);
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
```

## Configuration

**File: `config/routes/api_platform.yaml`**
```yaml
api_platform:
    resource: .
    type: api_platform
    prefix: /api
```

**File: `config/packages/api_platform.yaml`**
```yaml
api_platform:
    title: HR Management API
    description: 'API for Human Resources Management System'
    version: 1.0.0
    openapi:
        contact:
            name: HR Management Team
        license:
            name: Proprietary
    defaults:
        stateless: true
        cache_headers:
            vary: ['Content-Type', 'Authorization', 'Origin']
    docs_formats:
        jsonld: ['application/ld+json']
        json: ['application/json']
        html: ['text/html']
```

## Resulting API Endpoints

The implementation creates the following documented endpoints:

1. **GET** `/api/v1/employees` - Get collection of employees
2. **GET** `/api/v1/employees/{id}` - Get single employee
3. **PATCH** `/api/v1/employees/{id}/status` - Change employee status
4. **DELETE** `/api/v1/employees/{uuid}` - Delete employee

## Key Points

1. **Use ApiResource for routing**: Don't use `#[Route]` attributes in controllers when integrating with API Platform
2. **Versioned paths**: Include version in `uriTemplate` (e.g., `/v1/employees`)
3. **Controller separation**: Controllers should only contain business logic
4. **Documentation**: API Platform automatically generates OpenAPI documentation
5. **Custom operations**: Use standalone ApiResource classes for operations not tied to entities

## Testing

Access the API documentation at:
- **Swagger UI**: `http://localhost:8000/api/docs.html`
- **OpenAPI JSON**: `http://localhost:8000/api/docs.json`
- **JSON-LD**: `http://localhost:8000/api/docs`

All custom endpoints will appear properly documented with versioned paths in the Swagger UI.