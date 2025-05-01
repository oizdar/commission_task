<?php

declare(strict_types=1);

namespace App\CommissionTask\Models;

/**
 * @template T of mixed
 */
interface CollectionInterface
{
    /**
     * Dodaje element do kolekcji.
     *
     * @param T $item
     */
    public function add(mixed $item): void;

    /**
     * Usuwa element z kolekcji.
     *
     * @param T $item
     */
    public function remove(mixed $item): void;

    /**
     * Sprawdza, czy kolekcja zawiera dany element.
     *
     * @param T $item
     */
    public function contains(mixed $item): bool;

    /**
     * Zwraca wszystkie elementy kolekcji.
     *
     * @return array<T>
     */
    public function all(): array;

    /**
     * Zwraca liczbę elementów w kolekcji.
     */
    public function count(): int;
}
