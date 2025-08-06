<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Application\Employee\Query\GetEmployeesQuery;
use Exception;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

class ListEmployeesController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
    }

    #[OA\Get(
        path: '/api/v1/employees',
        description: 'Retrieves a paginated list of employees with optional filtering',
        summary: 'List employees',
        tags: ['Employee'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: 'Page number (default: 1)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, default: 1)
            ),
            new OA\Parameter(
                name: 'limit',
                description: 'Number of items per page (default: 10, max: 100)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100,
                    default: 10)
            ),
            new OA\Parameter(
                name: 'department',
                description: 'Filter by department',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: [
                    'HR', 'Engineering', 'Marketing', 'Sales'
                ])
            ),
            new OA\Parameter(
                name: 'role',
                description: 'Filter by role',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: [
                    'Developer', 'Senior Developer', 'Tech Lead', 'Manager',
                    'HR Specialist'
                ])
            ),
            new OA\Parameter(
                name: 'status',
                description: 'Filter by status',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: [
                    'ACTIVE', 'INACTIVE', 'TERMINATED'
                ])
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of employees retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id',
                                        type: 'string', example: 'uuid'),
                                    new OA\Property(property: 'name',
                                        type: 'string', example: 'John Doe'),
                                    new OA\Property(property: 'email',
                                        type: 'string',
                                        example: 'john@doe.com'),
                                    new OA\Property(property: 'role',
                                        type: 'string', example: 'Developer'),
                                    new OA\Property(property: 'department',
                                        type: 'string', example: 'Engineering'),
                                    new OA\Property(property: 'status',
                                        type: 'string', example: 'ACTIVE')
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'pagination',
                            properties: [
                                new OA\Property(property: 'page',
                                    type: 'integer', example: 1),
                                new OA\Property(property: 'limit',
                                    type: 'integer', example: 10),
                                new OA\Property(property: 'total',
                                    type: 'integer', example: 50),
                                new OA\Property(property: 'pages',
                                    type: 'integer', example: 5)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 400,
                description: 'Invalid query parameters')
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        // Get pagination parameters
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 10)));

        // Build filters array
        $filters = [];

        if ($request->query->has('department') && !empty($request->query->get('department'))) {
            $filters['department'] = $request->query->get('department');
        }

        if ($request->query->has('role') && !empty($request->query->get('role'))) {
            $filters['role'] = $request->query->get('role');
        }

        if ($request->query->has('status') && !empty($request->query->get('status'))) {
            $filters['status'] = $request->query->get('status');
        }

        $query = new GetEmployeesQuery($page, $limit, $filters);

        try {
            $envelope = $this->messageBus->dispatch($query);

            /** @var HandledStamp $handledStamp */
            $handledStamp = $envelope->last(HandledStamp::class);
            $result = $handledStamp?->getResult();

            if (!$result) {
                return new JsonResponse([
                    'data' => [],
                    'pagination' => [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => 0,
                        'pages' => 0
                    ]
                ]);
            }

            return new JsonResponse($result);
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}