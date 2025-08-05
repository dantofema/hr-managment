<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Application\Payroll\Command\CreateSalaryCommand;
use App\Application\Payroll\Query\GetSalaryByEmployeeIdQuery;
use App\Domain\Payroll\Repository\SalaryRepositoryInterface;
use InvalidArgumentException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/salaries', name: 'api_salary_')]
class SalaryController extends AbstractController
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private SalaryRepositoryInterface $salaryRepository
    ) {
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/salaries',
        summary: 'Create salary for an employee',
        description: 'Creates a new salary record with base salary and optional bonus for an employee',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['employee_id', 'base_salary'],
                properties: [
                    new OA\Property(property: 'employee_id', type: 'string', format: 'uuid', description: 'Employee UUID'),
                    new OA\Property(property: 'base_salary', type: 'number', format: 'float', description: 'Base salary amount'),
                    new OA\Property(property: 'bonus', type: 'number', format: 'float', description: 'Bonus amount (optional, defaults to 0)'),
                    new OA\Property(property: 'currency', type: 'string', description: 'Currency code (optional, defaults to USD)')
                ]
            )
        ),
        tags: ['Salaries'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Salary created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Bad request - validation error or salary already exists'),
            new OA\Response(response: 500, description: 'Internal server error')
        ]
    )]
    public function createSalary(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['employee_id']) || !isset($data['base_salary'])) {
                return $this->json([
                    'error' => 'employee_id and base_salary are required'
                ], Response::HTTP_BAD_REQUEST);
            }

            $command = new CreateSalaryCommand(
                $data['employee_id'],
                (float) $data['base_salary'],
                (float) ($data['bonus'] ?? 0.0),
                $data['currency'] ?? 'USD'
            );

            $this->messageBus->dispatch($command);

            return $this->json([
                'message' => 'Salary created successfully'
            ], Response::HTTP_CREATED);

        } catch (InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/employee/{employeeId}', name: 'get_by_employee', methods: ['GET'])]
    #[OA\Get(
        path: '/api/salaries/employee/{employeeId}',
        summary: 'Get salary by employee ID',
        description: 'Retrieves the salary information for a specific employee',
        tags: ['Salaries'],
        parameters: [
            new OA\Parameter(
                name: 'employeeId',
                in: 'path',
                required: true,
                description: 'Employee UUID',
                schema: new OA\Schema(type: 'string', format: 'uuid')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Salary retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'salary', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'No salary found for this employee'),
            new OA\Response(response: 400, description: 'Bad request - invalid employee ID'),
            new OA\Response(response: 500, description: 'Internal server error')
        ]
    )]
    public function getSalaryByEmployee(string $employeeId): JsonResponse
    {
        try {
            $query = new GetSalaryByEmployeeIdQuery($employeeId);
            $envelope = $this->messageBus->dispatch($query);
            $handledStamp = $envelope->last(HandledStamp::class);
            $salary = $handledStamp->getResult();

            if (!$salary) {
                return $this->json([
                    'error' => 'No salary found for this employee'
                ], Response::HTTP_NOT_FOUND);
            }

            return $this->json([
                'salary' => $salary->toArray()
            ]);

        } catch (InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    #[OA\Get(
        path: '/api/salaries',
        summary: 'Get all salaries',
        description: 'Retrieves all salary records in the system',
        tags: ['Salaries'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'All salaries retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'salaries', type: 'array', items: new OA\Items(type: 'object'))
                    ]
                )
            ),
            new OA\Response(response: 500, description: 'Internal server error')
        ]
    )]
    public function getAllSalaries(): JsonResponse
    {
        try {
            $salaries = $this->salaryRepository->findAll();
            $salariesData = array_map(fn($salary) => $salary->toArray(), $salaries);

            return $this->json([
                'salaries' => $salariesData
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}