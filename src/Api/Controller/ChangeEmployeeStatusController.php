<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Application\Employee\Command\ChangeEmployeeStatus\ChangeEmployeeStatusCommand;
use App\Domain\Employee\ValueObject\EmployeeStatus;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use ValueError;

class ChangeEmployeeStatusController extends AbstractController
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private ValidatorInterface $validator
    ) {
    }

    public function __invoke(string $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse([
                'error' => 'Invalid JSON format: ' . json_last_error_msg()
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!isset($data['status'])) {
            return new JsonResponse(['error' => 'Status is required'],
                Response::HTTP_BAD_REQUEST);
        }

        try {
            $newStatus = EmployeeStatus::from($data['status']);
        } catch (ValueError $e) {
            return new JsonResponse([
                'error' => 'Invalid status. Valid statuses are: ACTIVE, INACTIVE, TERMINATED'
            ], Response::HTTP_BAD_REQUEST);
        }

        $command = new ChangeEmployeeStatusCommand($id, $newStatus);

        try {
            $this->messageBus->dispatch($command);

            return new JsonResponse([
                'message' => 'Employee status changed successfully',
                'employee_id' => $id,
                'new_status' => $newStatus->value
            ]);
        } catch (Exception $e) {
            // Check if it's an "Employee not found" error
            if (str_contains($e->getMessage(), 'Employee not found')) {
                return new JsonResponse([
                    'error' => $e->getMessage()
                ], Response::HTTP_NOT_FOUND);
            }
            
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
