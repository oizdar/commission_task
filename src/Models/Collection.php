<?php

declare(strict_types=1);

namespace App\CommissionTask\Models;

/**
 * @template TValue of mixed
 *
 * @implements CollectionInterface<TValue>
 * @implements \IteratorAggregate<int, TValue>
 */
class Collection implements CollectionInterface, \IteratorAggregate
{
    /**
     * @var array<int, TValue>
     */
    protected array $items = [];

    /**
     * @param array<TValue> $items
     */
    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            $this->add($item);
        }
    }

    /**
     * @param TValue $item
     */
    public function add(mixed $item): void
    {
        $this->items[] = $item;
    }

    /**
     * @return TValue|null
     */
    public function get(int $index): mixed
    {
        return $this->items[$index] ?? null;
    }

    /**
     * @param TValue $item
     */
    public function remove(mixed $item): void
    {
        $this->items = array_filter($this->items, fn ($i) => $i !== $item);
    }

    /**
     * @param TValue $item
     */
    public function contains(mixed $item): bool
    {
        return in_array($item, $this->items, true);
    }

    /**
     * @return array<TValue>
     */
    public function all(): array
    {
        return array_values($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return \Traversable<int, TValue>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }
}
