<?php

declare(strict_types=1);

namespace App\Employee\Infrastructure\Controller;

use App\Employee\Application\Query\GetEmployeesQuery;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/employees', methods: ['GET'])]
#[OA\Get(
    path: '/api/v1/employees',
    summary: 'Get list of employees',
    description: 'Retrieve a paginated list of employees with optional filtering',
    tags: ['Employees']
)]
#[OA\Parameter(
    name: 'page',
    description: 'Page number for pagination',
    in: 'query',
    required: false,
    schema: new OA\Schema(type: 'integer', minimum: 1, default: 1)
)]
#[OA\Parameter(
    name: 'limit',
    description: 'Number of items per page',
    in: 'query',
    required: false,
    schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 10)
)]
#[OA\Parameter(
    name: 'department',
    description: 'Filter by department',
    in: 'query',
    required: false,
    schema: new OA\Schema(type: 'string', enum: ['HR', 'Engineering', 'Marketing', 'Sales'])
)]
#[OA\Parameter(
    name: 'status',
    description: 'Filter by employee status',
    in: 'query',
    required: false,
    schema: new OA\Schema(type: 'string', enum: ['ACTIVE', 'INACTIVE'])
)]
#[OA\Parameter(
    name: 'role',
    description: 'Filter by employee role',
    in: 'query',
    required: false,
    schema: new OA\Schema(type: 'string', enum: ['Developer', 'Senior Developer', 'Tech Lead', 'Manager', 'HR Specialist'])
)]
#[OA\Response(
    response: 200,
    description: 'List of employees retrieved successfully',
    content: new OA\JsonContent(
        type: 'array',
        items: new OA\Items(
            properties: [
                'id' => new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                'name' => new OA\Property(property: 'name', type: 'string'),
                'email' => new OA\Property(property: 'email', type: 'string', format: 'email'),
                'department' => new OA\Property(property: 'department', type: 'string'),
                'role' => new OA\Property(property: 'role', type: 'string'),
                'status' => new OA\Property(property: 'status', type: 'string'),
            ]
        )
    )
)]
#[OA\Response(
    response: 400,
    description: 'Bad request - Invalid parameters',
    content: new OA\JsonContent(
        properties: [
            'error' => new OA\Property(property: 'error', type: 'string')
        ]
    )
)]
final class GetEmployeesController extends AbstractController
{
    public function __construct(private readonly MessageBusInterface $queryBus)
    {
    }

    public function __invoke(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $filters = [
            'department' => $request->query->get('department'),
            'status' => $request->query->get('status'),
            'role' => $request->query->get('role'),
        ];

        $query = new GetEmployeesQuery($page, $limit, array_filter($filters));

        $envelope = $this->queryBus->dispatch($query);
        $handledStamp = $envelope->last(HandledStamp::class);
        $employees = $handledStamp->getResult();

        return $this->json($employees);
    }
}

