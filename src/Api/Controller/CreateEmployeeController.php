<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Application\Employee\Command\CreateEmployee\CreateEmployeeCommand;
use App\Domain\Employee\ValueObject\Department;
use App\Domain\Employee\ValueObject\Role;
use Exception;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use ValueError;

class CreateEmployeeController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
    }

    #[Route('/api/v1/employees', name: 'create_employee', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/employees',
        description: 'Creates a new employee with the provided information',
        summary: 'Create a new employee',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'role', 'department'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        description: 'Employee full name',
                        example: 'John Doe'
                    ),
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        description: 'Employee email address',
                        example: 'john@doe.com'
                    ),
                    new OA\Property(
                        property: 'role',
                        type: 'string',
                        enum: [
                            'Developer', 'Senior Developer', 'Tech Lead',
                            'Manager', 'HR Specialist'
                        ],
                        description: 'Employee role',
                        example: 'Developer'
                    ),
                    new OA\Property(
                        property: 'department',
                        type: 'string',
                        enum: ['HR', 'Engineering', 'Marketing', 'Sales'],
                        description: 'Employee department',
                        example: 'Engineering'
                    )
                ]
            )
        ),
        tags: ['Employee'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Employee created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string',
                            example: 'Employee created successfully'),
                        new OA\Property(property: 'employee_id', type: 'string',
                            example: 'uuid')
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid input data'),
            new OA\Response(response: 422, description: 'Validation error')
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate required fields
        $requiredFields = ['name', 'email', 'role', 'department'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                return new JsonResponse([
                    'error' => "Field '{$field}' is required"
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse([
                'error' => 'Invalid email format'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $department = Department::from($data['department']);
        } catch (ValueError $e) {
            return new JsonResponse([
                'error' => 'Invalid department. Valid departments are: '.implode(', ',
                        array_column(Department::cases(), 'value'))
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $role = Role::from($data['role']);
        } catch (ValueError $e) {
            return new JsonResponse([
                'error' => 'Invalid role. Valid roles are: '.implode(', ',
                        array_column(Role::cases(), 'value'))
            ], Response::HTTP_BAD_REQUEST);
        }

        $command = new CreateEmployeeCommand(
            trim($data['name']),
            trim($data['email']),
            $department,
            $role
        );

        try {
            $result = $this->messageBus->dispatch($command);

            // Extract employee ID from the result if available
            $employeeId = null;
            if (method_exists($result, 'getReturnValue')) {
                $employeeId = $result->getReturnValue();
            }

            return new JsonResponse([
                'message' => 'Employee created successfully',
                'employee_id' => $employeeId
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}