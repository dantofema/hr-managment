<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;

#[ApiResource(
    uriTemplate: '/v1/employees/{uuid}',
    operations: [
        new Delete(
            uriTemplate: '/v1/employees/{uuid}',
            controller: 'App\Api\Controller\DeleteEmployeeController'
        )
    ]
)]
class DeleteEmployee
{
    public function __construct(
        public string $uuid
    ) {
    }
}