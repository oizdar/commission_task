<?php

declare(strict_types=1);

namespace App\CommissionTask\Models;

use App\CommissionTask\Enums\OperationType;
use App\CommissionTask\Enums\UserType;
use Money\Money;

class Operation
{
    public function __construct(
        protected \DateTimeImmutable $date,
        protected int $userId,
        protected UserType $userType,
        protected OperationType $type,
        protected Money $amount,
    ) {
    }
}
