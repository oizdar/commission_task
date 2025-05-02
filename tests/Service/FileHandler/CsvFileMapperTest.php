<?php

declare(strict_types=1);

namespace App\CommissionTask\Tests\Service\FileHandler;

use App\CommissionTask\Enums\OperationType;
use App\CommissionTask\Enums\UserType;
use App\CommissionTask\Models\Operation;
use App\CommissionTask\Models\OperationsCollection;
use App\CommissionTask\Service\DataMapper\OperationsCSVDataMapper;
use App\CommissionTask\Service\FileHandler\CsvFileMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass('App\CommissionTask\Service\FileHandler\CsvFileMapper')]
class CsvFileMapperTest extends TestCase
{
    public function testLoadOperations(): void
    {
        $filePath = __DIR__ . '/../../resources/csv_file_handler_test.csv';

        $csvFileMapper = new CsvFileMapper(new OperationsCSVDataMapper(), new OperationsCollection(), $filePath);

        $result = $csvFileMapper->load();

        $this->assertTrue($result instanceof OperationsCollection);


        $expectedResult = new OperationsCollection([
            new Operation(
                new \DateTimeImmutable('2016-01-05'),
                '4',
                UserType::Private,
                OperationType::Withdraw,
                '1000.00',
                'EUR'
            ),
            new Operation(
                new \DateTimeImmutable('2016-01-05'),
                '1',
                UserType::Private,
                OperationType::Deposit,
                '200.00',
                'EUR'
            ),
            new Operation(
                new \DateTimeImmutable('2016-01-06'),
                '2',
                UserType::Business,
                OperationType::Withdraw,
                '300.00',
                'EUR'
            ),
        ]);

        $this->assertEquals($expectedResult, $result);
    }
}
