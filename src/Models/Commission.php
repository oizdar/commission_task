<?php

declare(strict_types=1);

namespace App\CommissionTask\Models;

use Money\Money;

readonly class Commission
{
    public function __construct(
        public Operation $operation,
        public Money $amount,
    ) {
    }
}
