<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Application\Employee\Query\GetEmployeeByIdQuery;
use App\ApiResource\Employee;
use Exception;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class GetEmployeeController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
    }

    #[OA\Get(
        path: '/api/v1/employees/{id}',
        description: 'Retrieves a single employee by ID',
        summary: 'Get employee by ID',
        tags: ['Employee'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Employee ID (UUID)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Employee retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'string', example: 'uuid'),
                        new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                        new OA\Property(property: 'email', type: 'string', example: 'john@doe.com'),
                        new OA\Property(property: 'role', type: 'string', example: 'Developer'),
                        new OA\Property(property: 'department', type: 'string', example: 'Engineering'),
                        new OA\Property(property: 'status', type: 'string', example: 'ACTIVE')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Employee not found'),
            new OA\Response(response: 400, description: 'Invalid employee ID format')
        ]
    )]
    public function __invoke(string $id): JsonResponse
    {
        try {
            $query = new GetEmployeeByIdQuery($id);
            $envelope = $this->messageBus->dispatch($query);

            /** @var HandledStamp $handledStamp */
            $handledStamp = $envelope->last(HandledStamp::class);
            $result = $handledStamp?->getResult();

            if (!$result) {
                return new JsonResponse([
                    'error' => 'Employee not found'
                ], Response::HTTP_NOT_FOUND);
            }

            // Return the employee data as JSON
            return new JsonResponse($result);
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}