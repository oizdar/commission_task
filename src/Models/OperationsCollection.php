<?php

declare(strict_types=1);

namespace App\CommissionTask\Models;

use Money\Converter;
use Money\Currency;
use Money\Money;

/**
 * @extends Collection<Operation>
 */
class OperationsCollection extends Collection
{
    public function add(mixed $item): void
    {
        if (!$item instanceof Operation) {
            throw new \InvalidArgumentException('Item must be an instance of Operation');
        }

        parent::add($item);
    }

    public function getSumInCurrency(Converter $converter, Currency $currency): Money
    {
        $sum = new Money(0, $currency);
        foreach ($this->items as $item) {
            if (!$item->amount->getCurrency()->equals($currency)) {
                $converted = $converter->convert($item->amount, $currency);
            }
            $sum = $sum->add($converted ?? $item->amount);
        }

        return $sum;
    }
}
