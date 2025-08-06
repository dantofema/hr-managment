<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Api\Dto\UpdateSalaryDto;
use App\Application\Payroll\Command\UpdateSalaryCommand;
use Exception;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Controller for updating employee salary information.
 * 
 * This controller handles PATCH requests to update salary data for existing employees.
 * It follows DDD principles by delegating business logic to the Application layer
 * through Command/Handler pattern and uses DTO for request validation.
 * 
 * The endpoint is documented with OpenAPI annotations and appears in the API documentation
 * under the Employee tag alongside other employee management endpoints.
 */
class UpdateEmployeeSalaryController extends AbstractController
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('/api/v1/employees/{id}/salary', name: 'update_employee_salary', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/v1/employees/{id}/salary',
        description: 'Updates the salary information for an existing employee. Allows updating base salary, bonus, and currency.',
        summary: 'Update employee salary',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'baseSalary',
                        description: 'Employee base salary amount',
                        type: 'number',
                        format: 'float',
                        example: 75000.00
                    ),
                    new OA\Property(
                        property: 'bonus',
                        description: 'Employee bonus amount (optional)',
                        type: 'number',
                        format: 'float',
                        example: 5000.00
                    ),
                    new OA\Property(
                        property: 'currency',
                        description: 'Currency code (3-letter ISO code)',
                        type: 'string',
                        example: 'USD'
                    )
                ]
            )
        ),
        tags: ['Employee'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Employee ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Salary updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string',
                            example: 'Salary updated successfully'),
                        new OA\Property(property: 'employee_id', type: 'string',
                            example: 'uuid')
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid input data or validation errors'),
            new OA\Response(response: 404, description: 'Employee not found or salary not found')
        ]
    )]
    /**
     * Updates the salary information for an existing employee.
     * 
     * This method handles the PATCH request to update employee salary data.
     * It validates the input using DTO validation, creates a command, and dispatches
     * it to the appropriate handler following CQRS pattern.
     * 
     * @param string $id The employee UUID identifier
     * @param Request $request The HTTP request containing salary update data
     * @return JsonResponse Success message with employee ID or error details
     */
    public function __invoke(string $id, Request $request): JsonResponse
    {
        try {
            // Deserialize request data to DTO
            $updateSalaryDto = $this->serializer->deserialize(
                $request->getContent(),
                UpdateSalaryDto::class,
                'json'
            );

            // Validate DTO
            $violations = $this->validator->validate($updateSalaryDto);
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }
                return new JsonResponse([
                    'error' => 'Validation failed',
                    'details' => $errors
                ], Response::HTTP_BAD_REQUEST);
            }

            // Create and dispatch command
            $command = new UpdateSalaryCommand(
                $id,
                $updateSalaryDto->baseSalary,
                $updateSalaryDto->bonus,
                $updateSalaryDto->currency
            );

            $this->messageBus->dispatch($command);

            return new JsonResponse([
                'message' => 'Salary updated successfully',
                'employee_id' => $id
            ]);

        } catch (Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}