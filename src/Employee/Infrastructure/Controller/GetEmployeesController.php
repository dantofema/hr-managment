<?php

declare(strict_types=1);

namespace App\Employee\Infrastructure\Controller;

use App\Employee\Application\Query\GetEmployeesQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/employees', methods: ['GET'])]
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

