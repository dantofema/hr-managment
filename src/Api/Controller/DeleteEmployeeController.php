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

    public function __invoke(string $uuid): JsonResponse
    {
        $command = new DeleteEmployeeCommand($uuid);

        try {
            $this->messageBus->dispatch($command);

            return new JsonResponse([
                'message' => 'Employee deleted successfully',
                'employee_id' => $uuid
            ]);
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}