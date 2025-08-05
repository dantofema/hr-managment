<?php

declare(strict_types=1);

namespace App\Employee\Infrastructure\Event;

use App\Employee\Domain\Event\EmployeeCreated;
use App\Employee\Domain\Event\EmployeeStatusChanged;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EmployeeEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EmployeeCreated::class => 'onEmployeeCreated',
            EmployeeStatusChanged::class => 'onEmployeeStatusChanged',
        ];
    }

    public function onEmployeeCreated(EmployeeCreated $event): void
    {
        $this->logger->info(sprintf(
            'Employee created with ID %s on %s',
            $event->employeeId,
            $event->occurredOn->format('Y-m-d H:i:s')
        ));
    }

    public function onEmployeeStatusChanged(EmployeeStatusChanged $event): void
    {
        $this->logger->info(sprintf(
            'Employee status changed for ID %s to %s on %s',
            $event->employeeId,
            $event->newStatus->value,
            $event->occurredOn->format('Y-m-d H:i:s')
        ));
    }
}

