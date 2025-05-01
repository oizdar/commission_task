<?php

declare(strict_types=1);

namespace App\CommissionTask\Models;

/**
 * @template T of mixed
 *
 * @implements CollectionInterface<T>
 */
class Collection implements CollectionInterface
{
    /**
     * @var array<T>
     */
    private array $items = [];

    /**
     * @param T $item
     */
    public function add(mixed $item): void
    {
        $this->items[] = $item;
    }

    /**
     * @param T $item
     */
    public function remove(mixed $item): void
    {
        $this->items = array_filter($this->items, fn ($i) => $i !== $item);
    }

    /**
     * @param T $item
     */
    public function contains(mixed $item): bool
    {
        return in_array($item, $this->items, true);
    }

    /**
     * @return array<T>
     */
    public function all(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }
}
