<?php

namespace App\CommissionTask\Tests\Services;

use App\CommissionTask\Enums\OperationType;
use App\CommissionTask\Enums\UserType;
use App\CommissionTask\Models\Collection;
use App\CommissionTask\Models\Commission;
use App\CommissionTask\Models\Operation;
use App\CommissionTask\Models\OperationsCollection;
use App\CommissionTask\Services\CommissionsCalculator;
use App\CommissionTask\Services\ExchangeRatesClient;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Exchange\FixedExchange;
use Money\Exchange\ReversedCurrenciesExchange;
use Money\Parser\DecimalMoneyParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CommissionsCalculator::class)]
class CommissionsCalculatorTest extends TestCase
{

    private ExchangeRatesClient $exchangeRatesClientMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->exchangeRatesClientMock = $this->createMock(ExchangeRatesClient::class);
        $this->exchangeRatesClientMock
            ->method('getRates')
            ->willReturn(new ReversedCurrenciesExchange(new FixedExchange([
                'EUR' => [
                    'USD' => '1.1497',
                    'JPY' => '129.53',
                ],
            ])));


    }

    #[DataProvider('oneOperationPerUser')]
    public function testOneCommissionPerUser(Operation $operation, Commission $expectedCommission): void
    {
        $calculator = new CommissionsCalculator($this->exchangeRatesClientMock);
        $commissions = $calculator->calculateCommissions(new OperationsCollection([$operation]));

        $this->assertEquals(1, $commissions->count());
        $this->assertEquals($expectedCommission, $commissions->get(0));
    }

    /**
     * @return array<array{0: Operation, 1: Commission}>
     */
    public static function oneOperationPerUser(): array
    {
        $currencies = new ISOCurrencies();
        $moneyParser = new DecimalMoneyParser($currencies);
        return [
            [
                $operation = new Operation(
                    new \DateTimeImmutable('2014-12-31'),
                    '1',
                    UserType::Private,
                    OperationType::Withdraw,
                    '1200.00',
                    'EUR'
                ),
                new Commission(
                    $operation,
                    $moneyParser->parse(
                        '0.60',
                        new Currency('EUR')
                    ),
                )
            ],
            [
                $operation = new Operation(
                    new \DateTimeImmutable('2014-12-31'),
                    '1',
                    UserType::Private,
                    OperationType::Withdraw,
                    '1000.00',
                    'JPY'
                ),
                new Commission(
                    $operation,
                    $moneyParser->parse(
                        '0',
                        new Currency('JPY')
                    ),
                ),
            ],
            [
                $operation = new Operation(
                    new \DateTimeImmutable('2016-01-05'),
                    '1',
                    UserType::Private,
                    OperationType::Deposit,
                    '200.00',
                    'USD'
                ),
                new Commission(
                    $operation,
                    $moneyParser->parse(
                        '0.06',
                        new Currency('USD')
                    ),
                )
            ],
            [
                $operation = new Operation(
                    new \DateTimeImmutable('2016-01-10'),
                    '1',
                    UserType::Business,
                    OperationType::Deposit,
                    '10000.00',
                    'EUR'
                ),
                new Commission(
                    $operation,
                    $moneyParser->parse(
                        '3.00',
                        new Currency('EUR')
                    ),
                )
            ],
        ];
    }

    /**
     * @param Collection<Commission> $expectedCommissions
     */
    #[DataProvider('multiplePrivateWithdrawOperationsPerUser')]
    public function testMultiplePrivateWithdrawalOperationsPerUser(OperationsCollection $operations, Collection $expectedCommissions): void
    {
        $calculator = new CommissionsCalculator($this->exchangeRatesClientMock);
        $commissions = $calculator->calculateCommissions($operations);
        foreach ($expectedCommissions as $key => $expectedCommission) {
            $this->assertEquals($expectedCommission, $commissions->get($key), 'Commission mismatch for operation ' . $key);
        }
    }

    /**
     * @return array<array{0: OperationsCollection, 1: Collection<Commission>}>
     */
    public static function multiplePrivateWithdrawOperationsPerUser(): array
    {
        $currencies = new ISOCurrencies();
        $moneyParser = new DecimalMoneyParser($currencies);
        return [
            [  //Fourth always has commission
                new OperationsCollection([
                    $operation1 = new Operation(
                        new \DateTimeImmutable('2014-12-31'),
                        '1',
                        UserType::Private,
                        OperationType::Withdraw,
                        '1.00',
                        'EUR'
                    ),
                    $operation2 = new Operation(
                        new \DateTimeImmutable('2014-12-31'),
                        '1',
                        UserType::Private,
                        OperationType::Withdraw,
                        '1.00',
                        'JPY'
                    ),
                    $operation3 = new Operation(
                        new \DateTimeImmutable('2014-12-31'),
                        '1',
                        UserType::Private,
                        OperationType::Withdraw,
                        '1.00',
                        'USD'
                    ),
                    $operation4 = new Operation(
                        new \DateTimeImmutable('2014-12-31'),
                        '1',
                        UserType::Private,
                        OperationType::Withdraw,
                        '100.00',
                        'EUR'
                    ),
                ]),
                new Collection([
                    new Commission(
                        $operation1,
                        $moneyParser->parse(
                            '0',
                            new Currency('EUR')
                        ),
                    ),
                    new Commission(
                        $operation2,
                        $moneyParser->parse(
                            '0',
                            new Currency('JPY')
                        ),
                    ),
                    new Commission(
                        $operation3,
                        $moneyParser->parse(
                            '0',
                            new Currency('USD')
                        ),
                    ),
                    new Commission(
                        $operation4,
                        $moneyParser->parse(
                            '0.30',
                            new Currency('EUR')
                        ),
                    ),
                ])
            ],
            [  // Commission for second - value extended
                new OperationsCollection([
                    $operation1 = new Operation(
                        new \DateTimeImmutable('2014-12-31'),
                        '1',
                        UserType::Private,
                        OperationType::Withdraw,
                        '500.00',
                        'EUR'
                    ),
                    $operation2 = new Operation(
                        new \DateTimeImmutable('2014-12-31'),
                        '1',
                        UserType::Private,
                        OperationType::Withdraw,
                        '700.00',
                        'EUR'
                    ),
                    $operation3 = new Operation(
                        new \DateTimeImmutable('2014-12-31'),
                        '1',
                        UserType::Private,
                        OperationType::Withdraw,
                        '100.00',
                        'USD'
                    ),
                ]),
                new Collection([
                    new Commission(
                        $operation1,
                        $moneyParser->parse(
                            '0',
                            new Currency('EUR')
                        ),
                    ),
                    new Commission(
                        $operation2,
                        $moneyParser->parse(
                            '0.60',
                            new Currency('EUR')
                        ),
                    ),
                    new Commission(
                        $operation3,
                        $moneyParser->parse(
                            '0.30',
                            new Currency('USD')
                        ),
                    ),
                ])
            ],
            [  // No commission in new week
                new OperationsCollection([
                    $operation1 = new Operation(
                        new \DateTimeImmutable('2024-12-31'),
                        '1',
                        UserType::Private,
                        OperationType::Withdraw,
                        '2000.00',
                        'EUR'
                    ),
                    $operation2 = new Operation(
                        new \DateTimeImmutable('2025-01-31'),
                        '1',
                        UserType::Private,
                        OperationType::Withdraw,
                        '1000.00',
                        'EUR'
                    ),
                ]),
                new Collection([
                    new Commission(
                        $operation1,
                        $moneyParser->parse(
                            '3.00',
                            new Currency('EUR')
                        ),
                    ),
                    new Commission(
                        $operation2,
                        $moneyParser->parse(
                            '0.00',
                            new Currency('EUR')
                        ),
                    ),
                ])
            ],
            [
                new OperationsCollection([
                    $operation1 = new Operation(
                        new \DateTimeImmutable('2016-01-06'),
                        '1',
                        UserType::Private,
                        OperationType::Withdraw,
                        '30000',
                        'JPY'
                    ),
                    $operation2 = new Operation(
                        new \DateTimeImmutable('2016-01-07'),
                        '1',
                        UserType::Private,
                        OperationType::Withdraw,
                        '1000.00',
                        'EUR'
                    ),
                    $operation3 = new Operation(
                        new \DateTimeImmutable('2016-01-07'),
                        '1',
                        UserType::Private,
                        OperationType::Withdraw,
                        '100.00',
                        'USD'
                    ),
                    $operation4 = new Operation(
                        new \DateTimeImmutable('2016-01-10'),
                        '1',
                        UserType::Private,
                        OperationType::Withdraw,
                        '100.00',
                        'EUR'
                    ),
                ]),
                new Collection([
                    new Commission(
                        $operation1,
                        $moneyParser->parse(
                            '0',
                            new Currency('JPY')
                        ),
                    ),
                    new Commission(
                        $operation2,
                        $moneyParser->parse(
                            '0.70',
                            new Currency('EUR')
                        ),
                    ),
                    new Commission(
                        $operation3,
                        $moneyParser->parse(
                            '0.30',
                            new Currency('USD')
                        ),
                    ),
                    new Commission(
                        $operation4,
                        $moneyParser->parse(
                            '0.30',
                            new Currency('EUR')
                        ),
                    ),
                ]),
            ],
        ];
    }
}
