<?php

declare(strict_types=1);

namespace App\CommissionTask\Helpers;

use Money\Currency;
use Money\Money;

class MoneyHelper
{
    public const int PERCENTAGE_DENOMINATOR = 100;

    public static function multiplyPercentageRoundUp(Money $value, float $percentage): Money
    {
        return $value->multiply((string) ($percentage / self::PERCENTAGE_DENOMINATOR), Money::ROUND_UP);
    }

    public static function zeroValue(Currency $currency): Money
    {
        return new Money(0, $currency);
    }
}
