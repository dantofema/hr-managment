<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Application\Payroll\Command\CalculatePayrollCommand;
use App\Application\Payroll\Query\GetPayrollsByEmployeeIdQuery;
use App\Domain\Payroll\Repository\PayrollRepositoryInterface;
use App\Domain\Payroll\ValueObject\PayrollId;
use Exception;
use InvalidArgumentException;
use OpenApi\Attributes as OA;
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
    #[OA\Post(
        path: '/api/payroll/calculate',
        description: 'Calculates payroll including taxes and deductions for a specific employee and period',
        summary: 'Calculate payroll for an employee',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['employee_id'],
                properties: [
                    new OA\Property(property: 'employee_id',
                        description: 'Employee UUID', type: 'string',
                        format: 'uuid'),
                    new OA\Property(property: 'period_start',
                        description: 'Period start date (optional, defaults to current month)',
                        type: 'string', format: 'date'),
                    new OA\Property(property: 'period_end',
                        description: 'Period end date (optional, defaults to current month)',
                        type: 'string', format: 'date')
                ]
            )
        ),
        tags: ['Payroll'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Payroll calculated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'payroll', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 400,
                description: 'Bad request - validation error'),
            new OA\Response(response: 500, description: 'Internal server error')
        ]
    )]
    public function calculatePayroll(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['employee_id'])) {
                return $this->json(['error' => 'employee_id is required'],
                    Response::HTTP_BAD_REQUEST);
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
            return $this->json(['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->json(['error' => 'Internal server error'],
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/employee/{employeeId}', name: 'get_by_employee', methods: ['GET'])]
    #[OA\Get(
        path: '/api/payroll/employee/{employeeId}',
        description: 'Retrieves all payroll records for a specific employee',
        summary: 'Get all payrolls for an employee',
        tags: ['Payroll'],
        parameters: [
            new OA\Parameter(
                name: 'employeeId',
                description: 'Employee UUID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Employee payrolls retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'employee_id',
                            type: 'string'),
                        new OA\Property(property: 'payrolls', type: 'array',
                            items: new OA\Items(type: 'object'))
                    ]
                )
            ),
            new OA\Response(response: 400,
                description: 'Bad request - invalid employee ID'),
            new OA\Response(response: 500, description: 'Internal server error')
        ]
    )]
    public function getPayrollsByEmployee(string $employeeId): JsonResponse
    {
        try {
            $query = new GetPayrollsByEmployeeIdQuery($employeeId);
            $envelope = $this->messageBus->dispatch($query);
            $handledStamp = $envelope->last(HandledStamp::class);
            $payrolls = $handledStamp->getResult();

            $payrollsData = array_map(fn($payroll) => $payroll->toArray(),
                $payrolls);

            return $this->json([
                'employee_id' => $employeeId,
                'payrolls' => $payrollsData
            ]);

        } catch (InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->json(['error' => 'Internal server error'],
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{payrollId}/receipt', name: 'get_receipt', methods: ['GET'])]
    #[OA\Get(
        path: '/api/payroll/{payrollId}/receipt',
        summary: 'Get payroll receipt',
        description: 'Generates and retrieves a detailed payroll receipt in JSON format',
        tags: ['Payroll'],
        parameters: [
            new OA\Parameter(
                name: 'payrollId',
                in: 'path',
                required: true,
                description: 'Payroll UUID',
                schema: new OA\Schema(type: 'string', format: 'uuid')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Payroll receipt generated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'receipt', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Payroll not found'),
            new OA\Response(response: 400,
                description: 'Bad request - invalid payroll ID'),
            new OA\Response(response: 500, description: 'Internal server error')
        ]
    )]
    public function getPayrollReceipt(string $payrollId): JsonResponse
    {
        try {
            $payroll = $this->payrollRepository->findById(PayrollId::fromString($payrollId));

            if (!$payroll) {
                return $this->json(['error' => 'Payroll not found'],
                    Response::HTTP_NOT_FOUND);
            }

            return $this->json([
                'receipt' => $payroll->generateReceipt()
            ]);

        } catch (InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->json(['error' => 'Internal server error'],
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    #[OA\Get(
        path: '/api/payroll',
        summary: 'Get all payrolls',
        description: 'Retrieves all payroll records in the system',
        tags: ['Payroll'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'All payrolls retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'payrolls', type: 'array',
                            items: new OA\Items(type: 'object'))
                    ]
                )
            ),
            new OA\Response(response: 500, description: 'Internal server error')
        ]
    )]
    public function getAllPayrolls(): JsonResponse
    {
        try {
            $payrolls = $this->payrollRepository->findAll();
            $payrollsData = array_map(fn($payroll) => $payroll->toArray(),
                $payrolls);

            return $this->json([
                'payrolls' => $payrollsData
            ]);

        } catch (Exception $e) {
            return $this->json(['error' => 'Internal server error'],
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}