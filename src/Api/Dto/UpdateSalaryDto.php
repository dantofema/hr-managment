<?php

declare(strict_types=1);

namespace App\Api\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateSalaryDto
{
    #[Assert\NotBlank(message: 'Base salary is required')]
    #[Assert\Type(type: 'float', message: 'Base salary must be a number')]
    #[Assert\GreaterThan(value: 0, message: 'Base salary must be greater than 0')]
    public ?float $baseSalary = null;

    #[Assert\Type(type: 'float', message: 'Bonus must be a number')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Bonus must be greater than or equal to 0')]
    public ?float $bonus = null;

    #[Assert\NotBlank(message: 'Currency is required')]
    #[Assert\Length(exactly: 3, exactMessage: 'Currency must be exactly 3 characters')]
    #[Assert\Regex(pattern: '/^[A-Z]{3}$/', message: 'Currency must be a valid 3-letter code (e.g., USD, EUR)')]
    public string $currency = 'USD';
}