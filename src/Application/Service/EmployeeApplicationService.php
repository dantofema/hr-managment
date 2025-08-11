<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\UseCase\Employee\CreateEmployee\CreateEmployeeCommand;
use App\Application\UseCase\Employee\CreateEmployee\CreateEmployeeHandler;
use App\Application\UseCase\Employee\CreateEmployee\CreateEmployeeResponse;
use App\Application\UseCase\Employee\GetEmployee\GetEmployeeQuery;
use App\Application\UseCase\Employee\GetEmployee\GetEmployeeHandler;
use App\Application\UseCase\Employee\GetEmployee\GetEmployeeResponse;
use App\Application\UseCase\Employee\ListEmployees\ListEmployeesQuery;
use App\Application\UseCase\Employee\ListEmployees\ListEmployeesHandler;
use App\Application\UseCase\Employee\ListEmployees\ListEmployeesResponse;

final readonly class EmployeeApplicationService
{
    public function __construct(
        private CreateEmployeeHandler $createEmployeeHandler,
        private GetEmployeeHandler $getEmployeeHandler,
        private ListEmployeesHandler $listEmployeesHandler
    ) {}

    public function createEmployee(CreateEmployeeCommand $command): CreateEmployeeResponse
    {
        return $this->createEmployeeHandler->handle($command);
    }

    public function getEmployee(GetEmployeeQuery $query): GetEmployeeResponse
    {
        return $this->getEmployeeHandler->handle($query);
    }

    public function listEmployees(ListEmployeesQuery $query): ListEmployeesResponse
    {
        return $this->listEmployeesHandler->handle($query);
    }
}