<?php

declare(strict_types=1);

namespace App\Employee\Infrastructure\Controller;

use App\Employee\Application\Query\GetEmployeeByIdQuery;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/employees/{id}', methods: ['GET'])]
final class GetEmployeeByIdController extends AbstractController
{
    public function __construct(private readonly MessageBusInterface $queryBus)
    {
    }

    public function __invoke(string $id): Response
    {
        $query = new GetEmployeeByIdQuery($id);

        try {
            $envelope = $this->queryBus->dispatch($query);
            $handledStamp = $envelope->last(HandledStamp::class);
            $employee = $handledStamp->getResult();
        } catch (Exception $e) {
            return $this->json(['error' => 'Employee not found'],
                Response::HTTP_NOT_FOUND);
        }

        return $this->json($employee);
    }
}

