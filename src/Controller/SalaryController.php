<?php

declare(strict_types=1);

namespace App\Controller;

use App\Payroll\Application\Command\CreateSalaryCommand;
use App\Payroll\Application\Query\GetSalaryByEmployeeIdQuery;
use App\Payroll\Domain\Repository\SalaryRepositoryInterface;
use InvalidArgumentException;
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