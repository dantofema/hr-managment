<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Api;

use App\Application\Service\EmployeeApplicationService;
use App\Application\UseCase\Employee\CreateEmployee\CreateEmployeeCommand;
use App\Application\UseCase\Employee\GetEmployee\GetEmployeeQuery;
use App\Infrastructure\ApiResource\Employee as EmployeeApiResource;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/employees')]
final class EmployeeController extends AbstractController
{
    public function __construct(
        private readonly EmployeeApplicationService $employeeService,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {}

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Basic validation
        if (!$data || !isset($data['firstName'], $data['lastName'], $data['email'], $data['position'], $data['salaryAmount'], $data['salaryCurrency'], $data['hiredAt'])) {
            return new JsonResponse(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $command = new CreateEmployeeCommand(
                $data['firstName'],
                $data['lastName'],
                $data['email'],
                $data['position'],
                (float) $data['salaryAmount'],
                $data['salaryCurrency'],
                new \DateTimeImmutable($data['hiredAt'])
            );

            $response = $this->employeeService->createEmployee($command);
            $apiResource = EmployeeApiResource::fromApplicationDTO($response);

            return new JsonResponse(
                $this->serializer->serialize($apiResource, 'json', ['groups' => ['employee:read']]),
                Response::HTTP_CREATED,
                [],
                true
            );
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        try {
            $query = new GetEmployeeQuery($id);
            $response = $this->employeeService->getEmployee($query);
            $apiResource = EmployeeApiResource::fromApplicationDTO($response);

            return new JsonResponse(
                $this->serializer->serialize($apiResource, 'json', ['groups' => ['employee:read', 'employee:item']]),
                Response::HTTP_OK,
                [],
                true
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => 'Employee not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}