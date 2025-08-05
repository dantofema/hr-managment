<?php

declare(strict_types=1);

namespace App\Employee\Infrastructure\Controller;

use App\Employee\Application\Command\ChangeEmployeeStatus\ChangeEmployeeStatusCommand;
use App\Employee\Domain\ValueObject\EmployeeStatus;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/employees/{id}/status', methods: ['PATCH'])]
final class ChangeEmployeeStatusController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $data = $request->toArray();

        $constraints = new Assert\Collection([
            'status' => [new Assert\NotBlank(),
                new Assert\Choice(choices: array_column(EmployeeStatus::cases(),
                    'value'))
            ],
        ]);

        $violations = $this->validator->validate($data, $constraints);

        if (count($violations) > 0) {
            return $this->json($violations, Response::HTTP_BAD_REQUEST);
        }

        $command = new ChangeEmployeeStatusCommand(
            $id,
            EmployeeStatus::from($data['status'])
        );

        try {
            $this->commandBus->dispatch($command);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()],
                Response::HTTP_NOT_FOUND);
        }

        return $this->json(['message' => 'Employee status updated successfully'],
            Response::HTTP_OK);
    }
}

