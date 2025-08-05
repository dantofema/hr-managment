<?php

namespace App\Employee\Infrastructure\Controller;

use App\Employee\Application\Command\DeleteEmployee\DeleteEmployeeCommand;
use App\Employee\Application\Handler\DeleteEmployee\DeleteEmployeeHandler;
use App\Employee\Domain\Exception\EmployeeNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class DeleteEmployeeController
{
    private DeleteEmployeeHandler $handler;

    public function __construct(DeleteEmployeeHandler $handler)
    {
        $this->handler = $handler;
    }

    #[Route('/employees/{id}', name: 'delete_employee', methods: ['DELETE'])]
    public function __invoke(string $id): Response
    {
        try {
            ($this->handler)(new DeleteEmployeeCommand($id));
            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (EmployeeNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }
}
