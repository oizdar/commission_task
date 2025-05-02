<?php

declare(strict_types=1);

namespace App\CommissionTask\Models;

use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;

readonly class Commission
{
    public function __construct(
        public Operation $operation,
        public Money $amount,
    ) {
    }

    public function getFormattedAmount(): string
    {
        $formatter = new DecimalMoneyFormatter(new ISOCurrencies());

        return $formatter->format($this->amount);
    }
}
