<?php

declare(strict_types=1);

namespace App\CommissionTask\Models;

use App\CommissionTask\Enums\OperationType;

/**
 * @extends Collection<Commission>
 */
class CommissionsCollection extends Collection
{
    public function add(mixed $item): void
    {
        if (!$item instanceof Commission) {
            throw new \InvalidArgumentException('Item must be an instance of Commission');
        }

        parent::add($item);
    }

    public function getWeeklyUserWithdrawals(Operation $operation): OperationsCollection
    {
        $startOfWeek = $operation->date->modify('monday this week');
        $endOfWeek = $operation->date->modify('sunday this week');

        // todo: performance refactor needed
        $filtered = array_filter($this->items,
            fn (Commission $item) => $item->operation->date >= $startOfWeek
                && $item->operation->date <= $endOfWeek
                && $item->operation->userId === $operation->userId
                && $item->operation->userType === $operation->userType
                && $item->operation->operationType === OperationType::Withdraw
        );

        return new OperationsCollection(array_map(fn (Commission $item) => $item->operation, $filtered));
    }
}
