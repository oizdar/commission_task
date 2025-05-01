<?php

declare(strict_types=1);

namespace App\CommissionTask\Models;

use App\CommissionTask\Enums\OperationType;
use App\CommissionTask\Enums\UserType;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Money;

readonly class Operation
{
    public Money $amount;

    public function __construct(
        public \DateTimeImmutable $date,
        public string $userId,
        public UserType $userType,
        public OperationType $type,
        string $amount,
        string $currency,
    ) {
        if (!is_numeric($amount)) {
            throw new \InvalidArgumentException('Amount must be a numeric value');
        }

        if (empty($currency)) {
            throw new \InvalidArgumentException('Currency must not be empty');
        }

        $currencies = new ISOCurrencies(); // todo: move to configuration which currencies are supported
        $currency = new Currency($currency);

        if (!$currencies->contains($currency)) {
            throw new \InvalidArgumentException('Currency not supported');
        }

        $this->amount = new Money($amount, $currency);

        if ($this->amount->lessThanOrEqual(new Money(0, $currency))) {
            throw new \InvalidArgumentException('Operation amount must be greater than zero');
        }
    }
}
