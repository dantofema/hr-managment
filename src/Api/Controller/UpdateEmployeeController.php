<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Application\Employee\Command\UpdateEmployee\UpdateEmployeeCommand;
use App\Domain\Employee\ValueObject\Department;
use App\Domain\Employee\ValueObject\Role;
use App\Domain\Employee\ValueObject\Salary;
use Exception;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use ValueError;

class UpdateEmployeeController extends AbstractController
{
    public function __construct(
        private MessageBusInterface $messageBus
    ) {
    }

    #[Route('/api/v1/employees/{id}', name: 'update_employee', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/v1/employees/{id}',
        description: 'Updates an existing employee with the provided information. All fields are optional for partial updates.',
        summary: 'Update an employee',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'name',
                        description: 'Employee full name',
                        type: 'string',
                        example: 'Jane Doe'
                    ),
                    new OA\Property(
                        property: 'email',
                        description: 'Employee email address',
                        type: 'string',
                        format: 'email',
                        example: 'jane@doe.com'
                    ),
                    new OA\Property(
                        property: 'role',
                        description: 'Employee role',
                        type: 'string',
                        enum: [
                            'Developer', 'Senior Developer', 'Tech Lead',
                            'Manager', 'HR Specialist'
                        ],
                        example: 'Manager'
                    ),
                    new OA\Property(
                        property: 'department',
                        description: 'Employee department',
                        type: 'string',
                        enum: ['HR', 'Engineering', 'Marketing', 'Sales'],
                        example: 'HR'
                    ),
                    new OA\Property(
                        property: 'salary',
                        description: 'Employee salary',
                        type: 'number',
                        format: 'float',
                        minimum: 0.01,
                        example: 50000.00
                    )
                ]
            )
        ),
        tags: ['Employee'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Employee UUID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Employee updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string',
                            example: 'Employee updated successfully'),
                        new OA\Property(property: 'employee_id', type: 'string',
                            example: 'uuid')
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid input data'),
            new OA\Response(response: 404, description: 'Employee not found')
        ]
    )]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate that at least one field is provided for update
        if (empty($data) || !array_intersect_key($data,
                array_flip(['name', 'email', 'role', 'department', 'salary']))) {
            return new JsonResponse([
                'error' => 'At least one field (name, email, role, department, salary) must be provided for update'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validate email format if provided
        if (isset($data['email']) && !empty($data['email']) && !filter_var($data['email'],
                FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse([
                'error' => 'Invalid email format'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validate salary if provided
        if (isset($data['salary']) && !empty($data['salary'])) {
            if (!is_numeric($data['salary']) || (float) $data['salary'] <= 0) {
                return new JsonResponse([
                    'error' => 'Salary must be a positive number'
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $department = null;
        if (isset($data['department']) && !empty($data['department'])) {
            try {
                $department = Department::from($data['department']);
            } catch (ValueError $e) {
                return new JsonResponse([
                    'error' => 'Invalid department. Valid departments are: '.implode(', ',
                            array_column(Department::cases(), 'value'))
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $role = null;
        if (isset($data['role']) && !empty($data['role'])) {
            try {
                $role = Role::from($data['role']);
            } catch (ValueError $e) {
                return new JsonResponse([
                    'error' => 'Invalid role. Valid roles are: '.implode(', ',
                            array_column(Role::cases(), 'value'))
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $salary = null;
        if (isset($data['salary']) && !empty($data['salary'])) {
            try {
                $salary = Salary::fromFloat((float) $data['salary']);
            } catch (Exception $e) {
                return new JsonResponse([
                    'error' => 'Invalid salary: ' . $e->getMessage()
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $command = new UpdateEmployeeCommand(
            $id,
            isset($data['name']) && !empty(trim($data['name'])) ? trim($data['name']) : null,
            isset($data['email']) && !empty(trim($data['email'])) ? trim($data['email']) : null,
            $department,
            $role,
            $salary
        );

        try {
            $this->messageBus->dispatch($command);

            return new JsonResponse([
                'message' => 'Employee updated successfully',
                'employee_id' => $id
            ]);
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}