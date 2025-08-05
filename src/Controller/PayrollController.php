<?php

declare(strict_types=1);

namespace App\Controller;

use App\Payroll\Application\Command\CalculatePayrollCommand;
use App\Payroll\Application\Query\GetPayrollsByEmployeeIdQuery;
use App\Payroll\Domain\Repository\PayrollRepositoryInterface;
use App\Payroll\Domain\ValueObject\PayrollId;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/payroll', name: 'api_payroll_')]
class PayrollController extends AbstractController
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private PayrollRepositoryInterface $payrollRepository
    ) {
    }

    #[Route('/calculate', name: 'calculate', methods: ['POST'])]
    public function calculatePayroll(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['employee_id'])) {
                return $this->json(['error' => 'employee_id is required'], Response::HTTP_BAD_REQUEST);
            }

            $command = new CalculatePayrollCommand(
                $data['employee_id'],
                $data['period_start'] ?? null,
                $data['period_end'] ?? null
            );

            $envelope = $this->messageBus->dispatch($command);
            $handledStamp = $envelope->last(HandledStamp::class);
            $payroll = $handledStamp->getResult();

            return $this->json([
                'message' => 'Payroll calculated successfully',
                'payroll' => $payroll->toArray()
            ], Response::HTTP_CREATED);

        } catch (InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/employee/{employeeId}', name: 'get_by_employee', methods: ['GET'])]
    public function getPayrollsByEmployee(string $employeeId): JsonResponse
    {
        try {
            $query = new GetPayrollsByEmployeeIdQuery($employeeId);
            $envelope = $this->messageBus->dispatch($query);
            $handledStamp = $envelope->last(HandledStamp::class);
            $payrolls = $handledStamp->getResult();

            $payrollsData = array_map(fn($payroll) => $payroll->toArray(), $payrolls);

            return $this->json([
                'employee_id' => $employeeId,
                'payrolls' => $payrollsData
            ]);

        } catch (InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{payrollId}/receipt', name: 'get_receipt', methods: ['GET'])]
    public function getPayrollReceipt(string $payrollId): JsonResponse
    {
        try {
            $payroll = $this->payrollRepository->findById(PayrollId::fromString($payrollId));
            
            if (!$payroll) {
                return $this->json(['error' => 'Payroll not found'], Response::HTTP_NOT_FOUND);
            }

            return $this->json([
                'receipt' => $payroll->generateReceipt()
            ]);

        } catch (InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAllPayrolls(): JsonResponse
    {
        try {
            $payrolls = $this->payrollRepository->findAll();
            $payrollsData = array_map(fn($payroll) => $payroll->toArray(), $payrolls);

            return $this->json([
                'payrolls' => $payrollsData
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}