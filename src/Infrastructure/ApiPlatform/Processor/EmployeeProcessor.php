<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Application\Service\EmployeeApplicationService;
use App\Application\UseCase\Employee\CreateEmployee\CreateEmployeeCommand;
use App\Infrastructure\ApiResource\Employee as EmployeeApiResource;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

final readonly class EmployeeProcessor implements ProcessorInterface
{
    public function __construct(
        private EmployeeApplicationService $employeeService
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof EmployeeApiResource) {
            throw new \InvalidArgumentException('Expected EmployeeApiResource');
        }

        try {
            $command = new CreateEmployeeCommand(
                $data->firstName,
                $data->lastName,
                $data->email,
                $data->position,
                $data->salaryAmount,
                $data->salaryCurrency,
                new \DateTimeImmutable($data->hiredAt)
            );

            $response = $this->employeeService->createEmployee($command);
            
            // Map CreateEmployeeResponse to EmployeeApiResource
            $apiResource = new EmployeeApiResource();
            $apiResource->id = $response->id;
            $apiResource->fullName = $response->fullName;
            $apiResource->email = $response->email;
            $apiResource->position = $response->position;
            $apiResource->salaryAmount = $response->salaryAmount;
            $apiResource->salaryCurrency = $response->salaryCurrency;
            $apiResource->hiredAt = $response->hiredAt;
            
            // Extract first and last name from full name
            $nameParts = explode(' ', $response->fullName, 2);
            $apiResource->firstName = $nameParts[0] ?? '';
            $apiResource->lastName = $nameParts[1] ?? '';
            
            return $apiResource;
        } catch (\InvalidArgumentException $e) {
            // Create a validation exception for duplicate email
            $violation = new ConstraintViolation(
                $e->getMessage(),
                null,
                [],
                $data->email,
                'email',
                $data->email
            );
            
            $violations = new ConstraintViolationList([$violation]);
            throw new ValidationException($violations);
        }
    }
}