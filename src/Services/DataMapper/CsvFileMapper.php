<?php

declare(strict_types=1);

namespace App\CommissionTask\Services\DataMapper;

use App\CommissionTask\Models\CollectionInterface;

/**
 * @template T of object
 * @template TCollection of CollectionInterface<T>
 */
class CsvFileMapper
{
    /**
     * @param MapperInterface<T> $mapper
     * @param CollectionInterface $collection
     */
    public function __construct(
        protected MapperInterface $mapper,
        protected CollectionInterface $collection,
        protected string $filePath,
    ) {
    }

    /**
     * @return CollectionInterface
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
