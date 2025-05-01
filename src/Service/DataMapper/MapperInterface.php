<?php

declare(strict_types=1);

namespace App\CommissionTask\Service\DataMapper;

/**
 * @template T of object
 */
interface MapperInterface
{
    /**
     * @param array<string|null> $row
     *
     * @return T of object
     */
    public function mapRow(array $row): object;
}
