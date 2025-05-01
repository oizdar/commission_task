<?php

declare(strict_types=1);

namespace App\CommissionTask\Enums;

enum OperationType: string
{
    case Withdraw = 'withdraw';
    case Deposit = 'deposit';
}
