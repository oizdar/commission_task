<?php

declare(strict_types=1);

namespace App\CommissionTask\Service\FileHandler;

use App\CommissionTask\Models\CollectionInterface;
use App\CommissionTask\Service\DataMapper\MapperInterface;

/**
 * @template T of object
 * @template TCollection of CollectionInterface<T>
 */
class CsvFileMapper
{
    /**
     * @param MapperInterface<T> $mapper
     * @param TCollection $collection
     */
    public function __construct(
        protected MapperInterface $mapper,
        protected CollectionInterface $collection,
        protected string $filePath,
    ) {
    }

    /**
     * @return TCollection
     */
    public function load(): CollectionInterface
    {
        $file = fopen($this->filePath, 'r');
        if ($file === false) {
            throw new \RuntimeException("Unable to open file: $this->filePath");
        }

        while (($row = fgetcsv($file)) !== false) {
            $this->collection->add($this->mapper->mapRow($row));
        }

        fclose($file);

        return $this->collection;
    }
}
