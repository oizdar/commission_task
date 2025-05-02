<?php
namespace App\CommissionTask\Tests\Models;

use App\CommissionTask\Enums\OperationType;
use App\CommissionTask\Enums\UserType;
use App\CommissionTask\Helpers\MoneyHelper;
use App\CommissionTask\Models\Commission;
use App\CommissionTask\Models\CommissionsCollection;
use App\CommissionTask\Models\Operation;
use Money\Currency;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CommissionsCollection::class)]
#[CoversClass(MoneyHelper::class)]
#[CoversClass(Commission::class)]
#[CoversClass(Operation::class)]
class CommissionsCollectionTest extends TestCase
{
    public function testAddOperation(): void
    {
        $commissionsCollection = new CommissionsCollection();
        $commission = new Commission(
            new Operation(
                new \DateTimeImmutable('2025-01-01'),
                '1',
                UserType::Private,
                OperationType::Deposit,
                '1000.00',
                'EUR'
            ),
            MoneyHelper::zeroValue(new Currency('EUR'))
        );


        $commissionsCollection->add($commission);

        $this->assertCount(1, $commissionsCollection);
        $this->assertEquals($commission, $commissionsCollection->get(0));
    }

    #[DataProvider('invalidValuesProvider')]
    public function testAddOtherObjectThrowsException(mixed $testValue): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $operationsCollection = new CommissionsCollection();
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
}
