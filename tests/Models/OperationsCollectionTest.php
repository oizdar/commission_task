<?php

namespace App\CommissionTask\Tests\Models;

use App\CommissionTask\Enums\OperationType;
use App\CommissionTask\Enums\UserType;
use App\CommissionTask\Helpers\MoneyHelper;
use App\CommissionTask\Models\Commission;
use App\CommissionTask\Models\CommissionsCollection;
use App\CommissionTask\Models\Operation;
use App\CommissionTask\Models\OperationsCollection;
use Money\Currency;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;


#[CoversClass(OperationsCollection::class)]
#[CoversClass(Operation::class)]
#[CoversClass(CommissionsCollection::class)]
#[CoversClass(Commission::class)]
#[CoversClass(MoneyHelper::class)]
class OperationsCollectionTest extends TestCase
{

    public function testAddOperation(): void
    {
        $operationsCollection = new OperationsCollection();
        $operation = new Operation(
            new \DateTimeImmutable('2025-01-01'),
            '1',
            UserType::Private,
            OperationType::Deposit,
            '1000.00',
            'EUR'
        );

        $operationsCollection->add($operation);

        $this->assertCount(1, $operationsCollection);
        $this->assertEquals($operation, $operationsCollection->get(0));
    }

    #[DataProvider('invalidValuesProvider')]
    public function testAddOtherObjectThrowsException(mixed $testValue): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $operationsCollection = new OperationsCollection();
        $operationsCollection->add($testValue); //@phpstan-ignore-line
    }

    /**
     * @return mixed[]
     */
    public static function invalidValuesProvider(): array
    {
        return [
            [new \stdClass()],
            ['string'],
            [123],
            [null],
            [3.14],
            [true],
            [[]],
        ];
    }


    public function testGetUserWeeklyWithdrawals(): void
    {
        $commissionsCollection = new CommissionsCollection();
        $commission1 = new Commission(
            new Operation(
                new \DateTimeImmutable('2025-04-01'), //tuesday first week
                '1',
                UserType::Private,
                OperationType::Withdraw,
                '1000.00',
                'EUR'
            ),
            MoneyHelper::zeroValue(new Currency('EUR'))
        );
        $commission2 = new Commission(
            new Operation(
                new \DateTimeImmutable('2025-04-01'), //tuesday first week
                '1',
                UserType::Private,
                OperationType::Deposit,
                '1000.00',
                'EUR'
            ),
            MoneyHelper::zeroValue(new Currency('EUR'))
        );

        $commission3 = new Commission(
            new Operation(
                new \DateTimeImmutable('2025-04-04'), //friday first week
                '1',
                UserType::Private,
                OperationType::Withdraw,
                '200.00',
                'EUR'
            ),
            MoneyHelper::zeroValue(new Currency('EUR'))
        );

        $commission4 = new Commission(
            new Operation(
                new \DateTimeImmutable('2025-04-07'), //monday second week
                '1',
                UserType::Private,
                OperationType::Withdraw,
                '200.00',
                'EUR'
            ),
            MoneyHelper::zeroValue(new Currency('EUR'))
        );
        $commission5 = new Commission(
            new Operation(
                new \DateTimeImmutable('2025-04-08'), //tuesday second week
                '1',
                UserType::Private,
                OperationType::Deposit,
                '200.00',
                'EUR'
            ),
            MoneyHelper::zeroValue(new Currency('EUR'))
        );


        $commissionsCollection->add($commission1);
        $commissionsCollection->add($commission2);
        $commissionsCollection->add($commission3);
        $commissionsCollection->add($commission4);
        $commissionsCollection->add($commission5);

        $weeklyWithdrawalsFirstWeek = $commissionsCollection->getWeeklyUserWithdrawals(new Operation(
            new \DateTimeImmutable('2025-04-06'), //sunday second week
            '1',
            UserType::Private,
            OperationType::Withdraw,
            '200.00',
            'EUR'
        ));


        $this->assertEquals(2, $weeklyWithdrawalsFirstWeek->count());

        $weeklyWithdrawalsSecondWeek = $commissionsCollection->getWeeklyUserWithdrawals(new Operation(
            new \DateTimeImmutable('2025-04-09'), //wednesday second week
            '1',
            UserType::Private,
            OperationType::Withdraw,
            '200.00',
            'EUR'
        ));

        $this->assertEquals(1, $weeklyWithdrawalsSecondWeek->count());
    }


}
