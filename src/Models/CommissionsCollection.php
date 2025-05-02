<?php

declare(strict_types=1);

namespace App\CommissionTask\Models;

use App\CommissionTask\Enums\OperationType;

/**
 * @extends Collection<Commission>
 */
class CommissionsCollection extends Collection
{
    /**
     * @var array<string, array<string, Commission[]>>
     */
    private array $groupedByUser = [];

    public function add(mixed $item): void
    {
        if (!$item instanceof Commission) {
            throw new \InvalidArgumentException('Item must be an instance of Commission');
        }

        parent::add($item);
        $this->addGroupedByUser($item);
    }

    /**
     * additional item references table created to speed up searches
     */
    private function addGroupedByUser(Commission $item): void
    {
        $this->groupedByUser[$item->operation->userId][$item->operation->userType->value] ??= [];
        $this->groupedByUser[$item->operation->userId][$item->operation->userType->value][] = $item;
    }

    public function getWeeklyUserWithdrawals(Operation $operation): OperationsCollection
    {
        $startOfWeek = $operation->date->modify('monday this week');
        $endOfWeek = $operation->date->modify('sunday this week');

        $filtered = array_filter($this->groupedByUser[$operation->userId][$operation->userType->value] ?? [],
            fn (Commission $item) => $item->operation->date >= $startOfWeek
                && $item->operation->date <= $endOfWeek
                && $item->operation->operationType === OperationType::Withdraw
        );

        return new OperationsCollection(array_map(fn (Commission $item) => $item->operation, $filtered));
    }
}
