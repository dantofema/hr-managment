<?php

declare(strict_types=1);

namespace App\Employee\Infrastructure\Controller;

use App\Employee\Application\Command\CreateEmployee\CreateEmployeeCommand;
use App\Employee\Domain\ValueObject\Department;
use App\Employee\Domain\ValueObject\Role;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/employees', methods: ['POST'])]
final class CreateEmployeeController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $data = $request->toArray();

        $constraints = new Assert\Collection([
            'name' => [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 2, 'max' => 100])
            ],
            'email' => [new Assert\NotBlank(), new Assert\Email()],
            'department' => [
                new Assert\NotBlank(),
                new Assert\Choice(choices: array_column(Department::cases(),
                    'value'))
            ],
            'role' => [
                new Assert\NotBlank(),
                new Assert\Choice(choices: array_column(Role::cases(), 'value'))
            ],
        ]);

        $violations = $this->validator->validate($data, $constraints);

        if (count($violations) > 0) {
            return $this->json($violations, Response::HTTP_BAD_REQUEST);
        }

        $command = new CreateEmployeeCommand(
            $data['name'],
            $data['email'],
            Department::from($data['department']),
            Role::from($data['role'])
        );

        $this->commandBus->dispatch($command);

        return $this->json(['message' => 'Employee created successfully'],
            Response::HTTP_CREATED);
    }
}

