<?php

declare(strict_types=1);

namespace App\Employee\Infrastructure\Controller;

use App\Employee\Application\Command\UpdateEmployee\UpdateEmployeeCommand;
use App\Employee\Domain\Exception\EmployeeNotFoundException;
use App\Employee\Domain\ValueObject\Department;
use App\Employee\Domain\ValueObject\Role;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/employees/{id}', methods: ['PUT', 'PATCH'])]
#[OA\Put(
    path: '/api/v1/employees/{id}',
    summary: 'Update employee',
    description: 'Update an existing employee with new data',
    tags: ['Employees']
)]
#[OA\Patch(
    path: '/api/v1/employees/{id}',
    summary: 'Partially update employee',
    description: 'Partially update an existing employee with new data',
    tags: ['Employees']
)]
#[OA\Parameter(
    name: 'id',
    description: 'Employee ID',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'string', format: 'uuid')
)]
#[OA\RequestBody(
    description: 'Employee data to update',
    required: true,
    content: new OA\JsonContent(
        properties: [
            'name' => new OA\Property(
                property: 'name',
                type: 'string',
                minLength: 2,
                maxLength: 100,
                example: 'John Doe'
            ),
            'email' => new OA\Property(
                property: 'email',
                type: 'string',
                format: 'email',
                example: 'john.doe@example.com'
            ),
            'department' => new OA\Property(
                property: 'department',
                type: 'string',
                enum: ['HR', 'Engineering', 'Marketing', 'Sales'],
                example: 'Engineering'
            ),
            'role' => new OA\Property(
                property: 'role',
                type: 'string',
                enum: ['Developer', 'Senior Developer', 'Tech Lead', 'Manager', 'HR Specialist'],
                example: 'Developer'
            ),
        ]
    )
)]
#[OA\Response(
    response: 200,
    description: 'Employee updated successfully',
    content: new OA\JsonContent(
        properties: [
            'message' => new OA\Property(property: 'message', type: 'string', example: 'Employee updated successfully')
        ]
    )
)]
#[OA\Response(
    response: 400,
    description: 'Bad request - Invalid data',
    content: new OA\JsonContent(
        properties: [
            'error' => new OA\Property(property: 'error', type: 'string'),
            'errors' => new OA\Property(
                property: 'errors',
                type: 'object',
                additionalProperties: new OA\AdditionalProperties(type: 'string')
            )
        ]
    )
)]
#[OA\Response(
    response: 404,
    description: 'Employee not found',
    content: new OA\JsonContent(
        properties: [
            'error' => new OA\Property(property: 'error', type: 'string', example: 'Employee with ID xxx not found')
        ]
    )
)]
#[OA\Response(
    response: 500,
    description: 'Internal server error',
    content: new OA\JsonContent(
        properties: [
            'error' => new OA\Property(property: 'error', type: 'string', example: 'Internal server error')
        ]
    )
)]
final class UpdateEmployeeController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function __invoke(string $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid JSON payload'], Response::HTTP_BAD_REQUEST);
        }

        // Validate input data
        $constraints = new Assert\Collection(
            fields: [
                'name' => new Assert\Optional([
                    new Assert\NotBlank(),
                    new Assert\Length(min: 2, max: 100),
                ]),
                'email' => new Assert\Optional([
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ]),
                'department' => new Assert\Optional([
                    new Assert\NotBlank(),
                    new Assert\Choice(choices: array_column(Department::cases(), 'value')),
                ]),
                'role' => new Assert\Optional([
                    new Assert\NotBlank(),
                    new Assert\Choice(choices: array_column(Role::cases(), 'value')),
                ]),
            ],
            allowExtraFields: false
        );

        $violations = $this->validator->validate($data, $constraints);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        try {
            $command = new UpdateEmployeeCommand(
                employeeId: $id,
                name: $data['name'] ?? null,
                email: $data['email'] ?? null,
                department: isset($data['department']) ? Department::from($data['department']) : null,
                role: isset($data['role']) ? Role::from($data['role']) : null,
            );

            $this->commandBus->dispatch($command);

            return $this->json(['message' => 'Employee updated successfully'], Response::HTTP_OK);

        } catch (EmployeeNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}