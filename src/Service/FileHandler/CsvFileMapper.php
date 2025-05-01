<?php

declare(strict_types=1);

namespace App\CommissionTask\Service\FileHandler;

use App\CommissionTask\Service\DataMapper\MapperInterface;

/**
 * @template T of object
 */
class CsvFileMapper
{
    /**
     * @param MapperInterface<T> $mapper
     */
    public function __construct(protected MapperInterface $mapper, protected string $filePath)
    {
    }

    /**
     * @return array<T>
     */
    public function load(): array
    {
        $file = fopen($this->filePath, 'r');
        if ($file === false) {
            throw new \RuntimeException("Unable to open file: $this->filePath");
        }

        $data = [];
        while (($row = fgetcsv($file)) !== false) {
            $data[] = $this->mapper->mapRow($row);
        }

        fclose($file);

        return $data;
    }
}
