<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Application\Employee\Command\DeleteEmployee\DeleteEmployeeCommand;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class DeleteEmployeeController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
    }

    public function __invoke(string $id): JsonResponse
    {
        $command = new DeleteEmployeeCommand($id);

        try {
            $this->messageBus->dispatch($command);

            return new JsonResponse([
                'message' => 'Employee deleted successfully',
                'employee_id' => $id
            ]);
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}