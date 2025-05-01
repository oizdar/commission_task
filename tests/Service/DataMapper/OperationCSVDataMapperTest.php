<?php

namespace App\CommissionTask\Tests\Service\DataMapper;

use App\CommissionTask\Enums\OperationType;
use App\CommissionTask\Enums\UserType;
use App\CommissionTask\Models\Operation;
use App\CommissionTask\Service\DataMapper\OperationsCSVDataMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(OperationsCSVDataMapper::class)]
class OperationCSVDataMapperTest extends TestCase
{

    private OperationsCSVDataMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new OperationsCSVDataMapper();
    }

    /**
     * @param string[] $row
     * @throws \DateMalformedStringException
     */
    #[DataProvider('ValidCsvData')]
    public function testFromCsvRow(array $row, Operation $expectedResult): void
    {
        $operation = $this->mapper->mapRow($row);

        $this->assertEquals($expectedResult->date, $operation->date);
        $this->assertEquals($expectedResult->userId, $operation->userId);
        $this->assertEquals($expectedResult->userType, $operation->userType);
        $this->assertEquals($expectedResult->type, $operation->type);
        $this->assertEquals($expectedResult->amount, $operation->amount);
    }

    /**
     * @return array<array{0: string[], 1: Operation}>
     */
    public static function ValidCsvData(): array
    {
        return [
            [
                [
                    '2025-01-01', // date
                    '1',        // userId
                    'private',    // userType
                    'withdraw',   // type
                    '1000.00',       // amount
                    'EUR'         // currency
                ],
                new Operation(
                    new \DateTimeImmutable('2025-01-01'),
                    '1',
                    UserType::Private,
                    OperationType::Withdraw,
                    '1000', 'EUR'
                )
            ],
            [
                [
                    '2025-07-01', // date
                    '2',        // userId
                    'business',    // userType
                    'deposit',   // type
                    '2000.00',       // amount
                    'USD'         // currency
                ],
                new Operation(
                    new \DateTimeImmutable('2025-07-01'),
                    '2',
                    UserType::Business,
                    OperationType::Deposit,
                    '2000',
                    'USD'
                )
            ]
        ];
    }

    /**
     * @param array<string|null> $row
     * @param \Throwable $expectedException
     */
    #[DataProvider('InvalidCsvData')]
    public function testInvalidDataHandling(array $row, \Throwable $expectedException): void
    {
        $this->expectException($expectedException::class);

        $this->mapper->mapRow($row);
    }

    /**
     * @return array<array{0: string[], 1: \Exception}>
     */
    public static function InvalidCsvData(): array
    {
        return [
            [
                [
                    '2025-01-01', // date
                    '1', // userId
                    'private', // userType
                    'withdraw', // type
                    'invalid_amount', // amount
                    'EUR' // currency
                ],
                new \InvalidArgumentException('Invalid amount format')
            ],
            [
                [
                    'invalid_date', // date
                    '1', // userId
                    'private', // userType
                    'withdraw', // type
                    '1000.00', // amount
                    'EUR' // currency
                ],
                new \InvalidArgumentException('Invalid date format')
            ],
            [
                [
                    '2025-01-01', // date
                    '1', // userId
                    'invalid_user_type', // userType
                    'withdraw', // type
                    '1000.00', // amount
                    'EUR' // currency
                ],
                new \InvalidArgumentException('Invalid user type')
            ],
            [
                [
                    '2025-01-01', // date
                    '1', // userId
                    'private', // userType
                    'invalid_operation_type', // type
                    '1000.00', // amount
                    'EUR' // currency
                ],
                new \InvalidArgumentException('Invalid operation type')
            ],
            [
                [
                    '2025-01-01', // date
                    '1', // userId
                    'private', // userType
                    'withdraw', // type
                    '1000.00', // amount
                    'invalid_currency' // currency
                ],
                new \InvalidArgumentException('Invalid currency format')
            ],
            [
                [
                    '2025-01-01', // date
                    '1', // userId
                    'private', // userType
                    'withdraw', // type
                    '-1000.00', // amount
                    'EUR' // currency
                ],
                new \InvalidArgumentException('Amount cannot be equal to or less than 0')
            ],
            [
                [
                    '2025-01-01', // date
                    '1', // userId
                    'private', // userType
                    'withdraw', // type
                    '0', // amount
                    'EUR' // currency
                ],
                new \InvalidArgumentException('Amount cannot be equal to or less than 0')
            ],

        ];
    }
}
